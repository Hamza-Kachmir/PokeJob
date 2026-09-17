<?php

namespace App\Controllers;

use App\Models\Job;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;
use PDO;

class DashboardController {
    private Job $jobModel;
    private array $statuses = ['JE_POSTULE', 'POSTULE', 'RELANCE', 'ENTRETIEN', 'REFUSE'];

    public function __construct(private PDO $pdo) {
        $this->jobModel = new Job($pdo);
    }

    public function index(): void {
        $jobs = isset($_SESSION['user_id']) ? $this->jobModel->getUserJobs((int)$_SESSION['user_id']) : [];
        $errorCode = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
        $jobLimitReached = $errorCode === 'job_limit';
        $errorMessage = match ($errorCode) {
            'invalid_job' => 'Les informations de la candidature sont invalides.',
            'duplicate' => 'Cette candidature existe déjà dans votre dashboard.',
            default => ''
        };
        require ROOT_PATH . '/app/Views/Dashboard/dashboard.php';
    }

    public function store(): void {
        Security::requireMethod('POST');
        $userId = $this->requireUser();
        Security::requireCsrf();
        $data = $this->validatedJobData();
        if ($data === null) {
            $this->redirectError('invalid_job');
        }
        if ($this->jobModel->duplicateCount($userId, $data['company_name'], $data['job_title']) > 0 && empty($_POST['allow_duplicate'])) {
            $this->redirectError('duplicate');
        }

        $this->pdo->beginTransaction();
        try {
            $jobCount = $this->jobModel->lockUserAndCountJobs($userId);
            if ($jobCount >= Job::MAX_JOBS_PER_USER) {
                $this->pdo->rollBack();
                $this->redirectError('job_limit');
            }
            if (!$this->jobModel->addJob($userId, $data)) {
                throw new \RuntimeException('Enregistrement de la candidature impossible.');
            }
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
        header('Location: /dashboard');
        exit;
    }

    public function updateStatus(): void {
        Security::requireMethod('POST', true);
        $userId = $this->requireUser(true);
        Security::requireCsrf();
        $jobId = filter_var($_POST['job_id'] ?? null, FILTER_VALIDATE_INT);
        $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
        $ok = $jobId && in_array($status, $this->statuses, true)
            ? $this->jobModel->updateJobStatus($jobId, $userId, $status)
            : false;
        $this->json(
            $ok ? ['success' => true] : ['success' => false, 'error' => 'Candidature introuvable'],
            $ok ? 200 : 404
        );
    }

    public function show(): void {
        Security::requireMethod('GET', true);
        $userId = $this->requireUser(true);
        $jobId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $job = $jobId ? $this->jobModel->getJobById($jobId, $userId) : false;
        $this->json(
            $job ? ['success' => true, 'data' => $job] : ['success' => false, 'error' => 'Candidature introuvable'],
            $job ? 200 : 404
        );
    }

    public function destroy(): void {
        Security::requireMethod('POST', true);
        $userId = $this->requireUser(true);
        Security::requireCsrf();
        $jobId = filter_var($_POST['job_id'] ?? null, FILTER_VALIDATE_INT);
        $ok = $jobId ? $this->jobModel->deleteJob($jobId, $userId) : false;
        $this->json(
            $ok ? ['success' => true] : ['success' => false, 'error' => 'Candidature introuvable'],
            $ok ? 200 : 404
        );
    }

    public function destroyAll(): void {
        Security::requireMethod('POST', true);
        $userId = $this->requireUser(true);
        Security::requireCsrf();

        $this->pdo->beginTransaction();
        try {
            $deleted = $this->jobModel->deleteAllJobs($userId);
            $this->pdo->commit();
            $this->json(['success' => true, 'deleted' => $deleted]);
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log($exception->getMessage());
            $this->json(['success' => false, 'error' => 'Suppression impossible'], 500);
        }
    }

    public function update(): void {
        Security::requireMethod('POST');
        $userId = $this->requireUser();
        Security::requireCsrf();
        $jobId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $data = $this->validatedJobData();
        if (!$jobId || $data === null) {
            $this->redirectError('invalid_job');
        }
        $currentJob = $this->jobModel->getJobById($jobId, $userId);
        if (!$currentJob) {
            $this->redirectError('invalid_job');
        }
        $duplicateKeyChanged = mb_strtolower(trim($data['company_name'])) !== mb_strtolower(trim((string)$currentJob['company_name']))
            || mb_strtolower(trim($data['job_title'])) !== mb_strtolower(trim((string)$currentJob['job_title']));
        if ($duplicateKeyChanged && $this->jobModel->duplicateCount($userId, $data['company_name'], $data['job_title'], $jobId) > 0 && empty($_POST['allow_duplicate'])) {
            $this->redirectError('duplicate');
        }

        $this->jobModel->updateJob($jobId, $userId, $data);
        header('Location: /dashboard');
        exit;
    }

    public function checkDuplicate(): void {
        Security::requireMethod('POST', true);
        $userId = $this->requireUser(true);
        Security::requireCsrf();
        $company = Validation::text($_POST['company_name'] ?? '', 100, true);
        $title = Validation::text($_POST['job_title'] ?? '', 150, true);
        if ($company === null || $title === null) {
            $this->json(['success' => false, 'error' => 'Données invalides'], 422);
        }
        $excludeId = filter_var($_POST['job_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $count = $this->jobModel->duplicateCount($userId, $company, $title, $excludeId);
        $this->json(['success' => true, 'duplicate' => $count > 0, 'count' => $count]);
    }

    public function importGuestJobs(): void {
        Security::requireMethod('POST', true);
        $userId = $this->requireUser(true);
        Security::requireCsrf();
        $payload = is_string($_POST['guest_jobs'] ?? null) ? $_POST['guest_jobs'] : '';
        $includeDuplicates = ($_POST['include_duplicates'] ?? '') === '1';
        $limiter = new RateLimiter($this->pdo);
        $allowedForUser = $limiter->allow(Security::identifierKey('job-import', (string)$userId), 10, 900);
        $allowedForIp = $limiter->allow(Security::clientKey('job-import-ip'), 20, 900);
        if (!$allowedForUser || !$allowedForIp) {
            $this->json(['success' => false, 'error' => 'Trop de tentatives d’import. Réessayez plus tard.'], 429);
        }

        // La taille du JSON et le nombre d’éléments traités sont strictement limités
        $decoded = strlen($payload) <= 1000000 ? json_decode($payload, true) : null;
        if (!is_array($decoded) || !array_is_list($decoded)) {
            $this->json(['success' => false, 'error' => 'Données invalides'], 422);
        }

        $duplicates = 0;
        $invalid = 0;
        $candidates = [];
        $candidateIndexes = [];
        $handledIndexes = [];
        $seenKeys = [];
        $consideredJobs = array_slice($decoded, 0, Job::MAX_JOBS_PER_USER);
        foreach ($consideredJobs as $index => $job) {
            $normalizedJob = $this->jobModel->normalizeGuestJob($job);
            if ($normalizedJob === null) {
                $invalid++;
                continue;
            }
            $company = $normalizedJob['company_name'];
            $title = $normalizedJob['job_title'];
            $duplicateKey = mb_strtolower(trim($company)) . "\0" . mb_strtolower(trim($title));
            $isDuplicate = isset($seenKeys[$duplicateKey])
                || $this->jobModel->duplicateCount($userId, $company, $title) > 0;
            if ($isDuplicate) {
                $duplicates++;
            }
            $seenKeys[$duplicateKey] = true;
            if (!$isDuplicate || $includeDuplicates) {
                $candidates[] = $normalizedJob;
                $candidateIndexes[] = $index;
            } else {
                $handledIndexes[] = $index;
            }
        }

        // Le premier passage compte les doublons sans encore modifier les données de l’utilisateur
        if (($_POST['preview'] ?? '') === '1') {
            $this->json([
                'success' => true,
                'total' => count($decoded),
                'considered' => count($consideredJobs),
                'remaining' => max(0, count($decoded) - count($consideredJobs)),
                'duplicates' => $duplicates,
                'invalid' => $invalid,
                'limit' => Job::MAX_JOBS_PER_USER,
                'capacity' => max(0, Job::MAX_JOBS_PER_USER - $this->jobModel->countUserJobs($userId))
            ]);
        }

        // L’import est atomique : une erreur annule toutes les candidatures de ce lot
        $this->pdo->beginTransaction();
        try {
            $jobCount = $this->jobModel->lockUserAndCountJobs($userId);
            $capacity = max(0, Job::MAX_JOBS_PER_USER - $jobCount);
            $limitedCandidates = array_slice($candidates, 0, $capacity);
            $limitedCandidateIndexes = array_slice($candidateIndexes, 0, $capacity);
            $limitReached = count($candidates) > $capacity;
            $result = $this->jobModel->importGuestJobs($userId, json_encode($limitedCandidates, JSON_UNESCAPED_UNICODE));
            if ($result['failed']) {
                throw new \RuntimeException('Import incomplet.');
            }
            $this->pdo->commit();
            $handledIndexes = array_values(array_unique(array_merge($handledIndexes, $limitedCandidateIndexes)));
            sort($handledIndexes);
            $this->json([
                'success' => true,
                'imported' => $result['imported'],
                'duplicates' => $duplicates,
                'invalid' => $invalid,
                'handled_indexes' => $handledIndexes,
                'remaining' => count($decoded) - count($handledIndexes),
                'limit' => Job::MAX_JOBS_PER_USER,
                'limit_reached' => $limitReached
            ]);
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log($exception->getMessage());
            $this->json(['success' => false, 'error' => 'Import impossible'], 500);
        }
    }

    private function validatedJobData(): ?array {
        $company = Validation::text($_POST['company_name'] ?? '', 100, true);
        $title = Validation::text($_POST['job_title'] ?? '', 150, true);
        $contactName = Validation::text($_POST['contact_name'] ?? '', 100);
        $phone = Validation::text($_POST['contact_phone'] ?? '', 20);
        $notes = Validation::text($_POST['notes_perso'] ?? '', 5000);
        $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
        $typeValue = is_string($_POST['type_candidature'] ?? null) ? $_POST['type_candidature'] : '';
        $type = $typeValue === '' ? null : $typeValue;
        $contactMailRaw = $_POST['contact_mail'] ?? '';
        $contactMail = $contactMailRaw === '' ? '' : Validation::email($contactMailRaw);
        $announcement = Validation::url($_POST['link_annonce'] ?? '');
        $linkedin = Validation::url($_POST['link_linkedin'] ?? '');
        $website = Validation::url($_POST['company_website'] ?? '');
        $dateAppliedRaw = $_POST['date_applied'] ?? '';
        $dateFollowUpRaw = $_POST['date_relance'] ?? '';
        $dateApplied = Validation::date($dateAppliedRaw);
        $dateFollowUp = Validation::date($dateFollowUpRaw);

        if (
            $company === null || $title === null || $contactName === null || $phone === null || $notes === null
            || $contactMail === null || $announcement === null || $linkedin === null || $website === null
            || !in_array($status, $this->statuses, true)
            || ($type !== null && !in_array($type, ['SPONTANEE', 'ANNONCE'], true))
            || ($dateAppliedRaw !== '' && $dateApplied === null)
            || ($dateFollowUpRaw !== '' && $dateFollowUp === null)
        ) {
            return null;
        }

        return [
            'company_name' => $company,
            'job_title' => $title,
            'contact_name' => $contactName,
            'contact_phone' => $phone,
            'contact_mail' => $contactMail,
            'link_annonce' => $announcement,
            'link_linkedin' => $linkedin,
            'date_applied' => $dateApplied,
            'date_relance' => $dateFollowUp,
            'notes_perso' => $notes,
            'company_website' => $website,
            'type_candidature' => $type,
            'status' => $status
        ];
    }

    private function requireUser(bool $json = false): int {
        if (!isset($_SESSION['user_id'])) {
            if ($json) {
                $this->json(['success' => false, 'error' => 'Non connecté'], 401);
            }
            header('Location: /login');
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function json(array $data, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function redirectError(string $error): never {
        header('Location: /dashboard?error=' . urlencode($error));
        exit;
    }
}
