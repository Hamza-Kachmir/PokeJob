<?php
class Job {
    protected $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function getUserJobs(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM jobs WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addJob($userId, $companyName, $status, $contactName = null, $contactPhone = null, $contactMail = null, $linkAnnonce = null, $linkLinkedin = null, $dateApplied = null, $dateRelance = null, $notesPerso = null, $companyWebsite = null, $typeCandidature = 'SPONTANEE') {
        $dateApplied = empty($dateApplied) ? null : $dateApplied;
        $dateRelance = empty($dateRelance) ? null : $dateRelance;

        $stmt = $this->db->prepare("
            INSERT INTO jobs 
            (user_id, company_name, status, contact_name, contact_phone, contact_mail, link_annonce, link_linkedin, date_applied, date_relance, notes_perso, company_website, type_candidature, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $userId, $companyName, $status, $contactName, $contactPhone, $contactMail,
            $linkAnnonce, $linkLinkedin, $dateApplied, $dateRelance, $notesPerso, $companyWebsite, $typeCandidature
        ]);
    }

    public function updateJobStatus(int $jobId, int $userId, string $newStatus): bool {
        $stmt = $this->db->prepare("UPDATE jobs SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['status' => $newStatus, 'id' => $jobId, 'user_id' => $userId]);
    }

    public function getJobById(int $jobId, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM jobs WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $jobId, 'user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteJob(int $jobId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM jobs WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $jobId, 'user_id' => $userId]);
    }

    public function updateJob(int $jobId, int $userId, $companyName, $status, $contactName, $contactPhone, $contactMail, $linkAnnonce, $linkLinkedin, $dateApplied, $dateRelance, $notesPerso, $companyWebsite, $typeCandidature): bool {
        $dateApplied = empty($dateApplied) ? null : $dateApplied;
        $dateRelance = empty($dateRelance) ? null : $dateRelance;

        $stmt = $this->db->prepare("
            UPDATE jobs SET 
                company_name = :company_name,
                status = :status,
                contact_name = :contact_name,
                contact_phone = :contact_phone,
                contact_mail = :contact_mail,
                link_annonce = :link_annonce,
                link_linkedin = :link_linkedin,
                date_applied = :date_applied,
                date_relance = :date_relance,
                notes_perso = :notes_perso,
                company_website = :company_website,
                type_candidature = :type_candidature,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'company_name'    => $companyName,
            'status'          => $status,
            'contact_name'    => $contactName,
            'contact_phone'   => $contactPhone,
            'contact_mail'    => $contactMail,
            'link_annonce'    => $linkAnnonce,
            'link_linkedin'   => $linkLinkedin,
            'date_applied'    => $dateApplied,
            'date_relance'    => $dateRelance,
            'notes_perso'     => $notesPerso,
            'company_website' => $companyWebsite,
            'type_candidature'=> $typeCandidature,
            'id'              => $jobId,
            'user_id'         => $userId
        ]);
    }
}
?>
