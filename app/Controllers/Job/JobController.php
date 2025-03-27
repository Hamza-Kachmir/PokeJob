<?php
namespace App\Controllers\Job;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/Job.php';
require_once __DIR__ . '/../../../app/Models/CompanyCounter.php';

class JobController {
    private $jobModel;
    private $companyCounter;

    // Constructeur pour initialiser les modèles
    public function __construct($pdo) {
        $this->jobModel = new \Job($pdo);
        $this->companyCounter = new \CompanyCounter($pdo);
    }

    // Affiche la liste des jobs de l'utilisateur
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=login');
            exit;
        }
        $jobs = $this->jobModel->getUserJobs($_SESSION['user_id']);
        $success_message = '';
        $error_message   = '';
        require __DIR__ . '/../../../app/Views/Job/job.php';
    }

    // Ajoute un nouveau job
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=login');
            exit;
        }
        $company_name = trim($_POST['company_name'] ?? '');
        if (empty($company_name)) {
            header('Location: /index.php?route=jobs&error=company_required');
            exit;
        }
        $userId           = $_SESSION['user_id'];
        $status           = $_POST['status'] ?? 'JE_POSTULE';
        $contact_name     = trim($_POST['contact_name'] ?? '');
        $contact_phone    = trim($_POST['contact_phone'] ?? '');
        $contact_mail     = trim($_POST['contact_mail'] ?? '');
        $link_annonce     = trim($_POST['link_annonce'] ?? '');
        $link_linkedin    = trim($_POST['link_linkedin'] ?? '');
        $date_applied     = $_POST['date_applied'] ?? null;
        $date_relance     = $_POST['date_relance'] ?? null;
        $notes_perso      = trim($_POST['notes_perso'] ?? '');
        $company_website  = trim($_POST['company_website'] ?? '');
        $type_candidature = $_POST['type_candidature'] ?? 'SPONTANEE';

        $this->jobModel->addJob(
            $userId,
            $company_name,
            $status,
            $contact_name,
            $contact_phone,
            $contact_mail,
            $link_annonce,
            $link_linkedin,
            $date_applied,
            $date_relance,
            $notes_perso,
            $company_website,
            $type_candidature
        );

        $this->companyCounter->increment();

        header('Location: /index.php?route=jobs');
        exit;
    }

    // Met à jour le statut d'un job
    public function updateStatus() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            exit;
        }
        $jobId     = $_POST['job_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'JE_POSTULE';
        $ok = $this->jobModel->updateJobStatus($jobId, $_SESSION['user_id'], $newStatus);
        echo json_encode(['success' => $ok]);
    }

    // Affiche les détails d'un job
    public function show() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            exit;
        }
        $jobId = $_GET['id'] ?? 0;
        $job   = $this->jobModel->getJobById($jobId, $_SESSION['user_id']);
        echo json_encode($job ? ['success' => true, 'data' => $job] : ['success' => false, 'error' => 'Introuvable']);
    }

    // Supprime un job
    public function destroy() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=login');
            exit;
        }
        $jobId = $_GET['id'] ?? 0;
        $ok = $this->jobModel->deleteJob($jobId, $_SESSION['user_id']);
        header('Location: /index.php?route=jobs' . ($ok ? '' : '&error=delete_failed'));
        exit;
    }

    // Met à jour les informations d'un job
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=login');
            exit;
        }
        $jobId = $_GET['id'] ?? 0;
        $company_name = trim($_POST['company_name'] ?? '');
        if (empty($company_name)) {
            header('Location: /index.php?route=jobs&error=company_required');
            exit;
        }
        $status           = $_POST['status'] ?? 'JE_POSTULE';
        $contact_name     = trim($_POST['contact_name'] ?? '');
        $contact_phone    = trim($_POST['contact_phone'] ?? '');
        $contact_mail     = trim($_POST['contact_mail'] ?? '');
        $link_annonce     = trim($_POST['link_annonce'] ?? '');
        $link_linkedin    = trim($_POST['link_linkedin'] ?? '');
        $date_applied     = $_POST['date_applied'] ?? null;
        $date_relance     = $_POST['date_relance'] ?? null;
        $notes_perso      = trim($_POST['notes_perso'] ?? '');
        $company_website  = trim($_POST['company_website'] ?? '');
        $type_candidature = $_POST['type_candidature'] ?? 'SPONTANEE';

        $this->jobModel->updateJob(
            $jobId,
            $_SESSION['user_id'],
            $company_name,
            $status,
            $contact_name,
            $contact_phone,
            $contact_mail,
            $link_annonce,
            $link_linkedin,
            $date_applied,
            $date_relance,
            $notes_perso,
            $company_website,
            $type_candidature
        );

        header('Location: /index.php?route=jobs');
        exit;
    }
}
?>