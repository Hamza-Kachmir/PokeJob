<?php

namespace App\Models;

use App\Support\Validation;
use PDO;

class Job {
    public const MAX_JOBS_PER_USER = 1000;

    public function __construct(private PDO $pdo) {}

    public function getUserJobs(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM jobs WHERE user_id = ? ORDER BY status_changed_at DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobById(int $jobId, int $userId): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM jobs WHERE id = ? AND user_id = ?");
        $stmt->execute([$jobId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countUserJobs(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM jobs WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function lockUserAndCountJobs(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn() === false) {
            throw new \RuntimeException('Utilisateur introuvable.');
        }
        return $this->countUserJobs($userId);
    }

    public function addJob(int $userId, array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO jobs (user_id, company_name, status, contact_name, contact_phone, contact_mail, link_annonce, link_linkedin, date_applied, date_relance, notes_perso, company_website, type_candidature, job_title) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $data['company_name'], $data['status'], $data['contact_name'], $data['contact_phone'], $data['contact_mail'], $data['link_annonce'], $data['link_linkedin'], $data['date_applied'], $data['date_relance'], $data['notes_perso'], $data['company_website'], $data['type_candidature'], $data['job_title']]);
    }

    public function updateJob(int $jobId, int $userId, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE jobs SET company_name = ?, status_changed_at = CASE WHEN status <> ? THEN CURRENT_TIMESTAMP(6) ELSE status_changed_at END, status = ?, contact_name = ?, contact_phone = ?, contact_mail = ?, link_annonce = ?, link_linkedin = ?, date_applied = ?, date_relance = ?, notes_perso = ?, company_website = ?, type_candidature = ?, job_title = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$data['company_name'], $data['status'], $data['status'], $data['contact_name'], $data['contact_phone'], $data['contact_mail'], $data['link_annonce'], $data['link_linkedin'], $data['date_applied'], $data['date_relance'], $data['notes_perso'], $data['company_website'], $data['type_candidature'], $data['job_title'], $jobId, $userId]);
    }

    public function updateJobStatus(int $jobId, int $userId, string $status): bool {
        $stmt = $this->pdo->prepare("UPDATE jobs SET status_changed_at = CASE WHEN status <> ? THEN CURRENT_TIMESTAMP(6) ELSE status_changed_at END, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$status, $status, $jobId, $userId]);
        return $stmt->rowCount() === 1 || $this->getJobById($jobId, $userId) !== false;
    }

    public function deleteJob(int $jobId, int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE id = ? AND user_id = ?");
        $stmt->execute([$jobId, $userId]);
        return $stmt->rowCount() === 1;
    }

    public function deleteAllJobs(int $userId): int {
        $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    public function duplicateCount(int $userId, string $companyName, string $jobTitle, ?int $excludeId = null): int {
        $sql = "SELECT COUNT(*) FROM jobs WHERE user_id = ? AND LOWER(TRIM(company_name)) = LOWER(TRIM(?)) AND LOWER(TRIM(job_title)) = LOWER(TRIM(?))";
        $params = [$userId, $companyName, $jobTitle];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function importGuestJobs(int $userId, string $guestJobsJson): array {
        // Les données locales viennent du navigateur et sont donc limitées puis normalisées avant insertion
        if ($guestJobsJson === '' || strlen($guestJobsJson) > 1000000) {
            return ['imported' => 0, 'failed' => false];
        }

        $decoded = json_decode($guestJobsJson, true);
        if (!is_array($decoded)) {
            return ['imported' => 0, 'failed' => false];
        }

        $imported = 0;

        foreach (array_slice($decoded, 0, self::MAX_JOBS_PER_USER) as $guestJob) {
            $normalizedJob = $this->normalizeGuestJob($guestJob);
            if ($normalizedJob === null) {
                return ['imported' => $imported, 'failed' => true];
            }

            try {
                $saved = $this->addJob($userId, $normalizedJob);
            } catch (\Throwable) {
                return ['imported' => $imported, 'failed' => true];
            }

            if (!$saved) {
                return ['imported' => $imported, 'failed' => true];
            }
            $imported++;
        }

        return ['imported' => $imported, 'failed' => false];
    }

    public function normalizeGuestJob(mixed $guestJob): ?array {
        if (!is_array($guestJob)) {
            return null;
        }

        $companyName = Validation::text($guestJob['company_name'] ?? '', 100, true);
        $jobTitle = Validation::text($guestJob['job_title'] ?? '', 150, true);
        $contactName = Validation::text($guestJob['contact_name'] ?? '', 100);
        $contactPhone = Validation::text($guestJob['contact_phone'] ?? '', 20);
        $notes = Validation::text($guestJob['notes_perso'] ?? '', 5000);
        $status = is_string($guestJob['status'] ?? null) ? $guestJob['status'] : '';
        $typeValue = is_string($guestJob['type_candidature'] ?? null) ? $guestJob['type_candidature'] : '';
        $type = $typeValue === '' ? null : $typeValue;
        $contactMailRaw = $guestJob['contact_mail'] ?? '';
        $contactMail = $contactMailRaw === '' ? '' : Validation::email($contactMailRaw);
        $announcement = Validation::url($guestJob['link_annonce'] ?? '');
        $linkedin = Validation::url($guestJob['link_linkedin'] ?? '');
        $website = Validation::url($guestJob['company_website'] ?? '');
        $dateAppliedRaw = $guestJob['date_applied'] ?? '';
        $dateRelanceRaw = $guestJob['date_relance'] ?? '';
        $dateApplied = Validation::date($dateAppliedRaw);
        $dateRelance = Validation::date($dateRelanceRaw);

        if (
            $companyName === null || $jobTitle === null || $contactName === null || $contactPhone === null || $notes === null
            || $contactMail === null || $announcement === null || $linkedin === null || $website === null
            || !in_array($status, ['JE_POSTULE', 'POSTULE', 'RELANCE', 'ENTRETIEN', 'REFUSE'], true)
            || ($type !== null && !in_array($type, ['SPONTANEE', 'ANNONCE'], true))
            || ($dateAppliedRaw !== '' && $dateApplied === null)
            || ($dateRelanceRaw !== '' && $dateRelance === null)
        ) {
            return null;
        }

        return [
            'company_name' => $companyName,
            'status' => $status,
            'contact_name' => $contactName,
            'contact_phone' => $contactPhone,
            'contact_mail' => $contactMail,
            'link_annonce' => $announcement,
            'link_linkedin' => $linkedin,
            'date_applied' => $dateApplied,
            'date_relance' => $dateRelance,
            'notes_perso' => $notes,
            'company_website' => $website,
            'type_candidature' => $type,
            'job_title' => $jobTitle
        ];
    }
}
