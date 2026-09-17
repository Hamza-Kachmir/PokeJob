<?php

namespace App\Services;

use App\Config\Environment;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class Mailer {
    public function sendCode(string $recipient, string $name, string $code, string $purpose): void {
        $mail = $this->createMessage();
        $mail->addAddress($recipient, $name);
        $mail->Subject = $purpose === 'VERIFY_EMAIL' ? 'Confirmez votre adresse e-mail PokéJob' : 'Votre code de réinitialisation PokéJob';
        $mail->Body = "Bonjour {$name},\n\nVotre code est : {$code}\n\nCe code expire dans 10 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail.";
        $mail->send();
    }

    public function sendContactMessage(string $firstName, string $lastName, string $email, string $subject, string $message): void {
        $mail = $this->createMessage();
        $recipient = Environment::isProduction()
            ? Environment::requireSecret('CONTACT_RECIPIENT', ['contact@pokejob.local'])
            : $this->env('CONTACT_RECIPIENT', 'contact@pokejob.local');
        $mail->addAddress($recipient, 'PokéJob');
        $mail->addReplyTo($email, trim($firstName . ' ' . $lastName));
        $mail->Subject = 'Nouveau message PokéJob : ' . $subject;
        $mail->Body = "Nouveau message reçu depuis PokéJob\n\nNom : {$firstName} {$lastName}\nE-mail : {$email}\nSujet : {$subject}\n\nMessage :\n{$message}";
        $mail->send();
    }

    private function createMessage(): PHPMailer {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer n’est pas installé.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->env('SMTP_HOST', 'mailpit');
        $mail->Port = (int)$this->env('SMTP_PORT', '1025');
        $mail->Timeout = 8;
        $mail->SMTPAuth = filter_var($this->env('SMTP_AUTH', 'false'), FILTER_VALIDATE_BOOL);

        if (Environment::isProduction()) {
            Environment::requireSecret('SMTP_HOST', ['mailpit']);
            Environment::requireSecret('MAIL_FROM_ADDRESS', ['noreply@pokejob.local', 'no-reply@pokejob.local']);
            if ($mail->SMTPAuth) {
                Environment::requireSecret('SMTP_USER');
                Environment::requireSecret('SMTP_PASSWORD');
            }
        }

        // Mailpit fonctionne sans authentification, contrairement aux fournisseurs SMTP de production
        if ($mail->SMTPAuth) {
            $mail->Username = $this->env('SMTP_USER');
            $mail->Password = $this->env('SMTP_PASSWORD');
            $encryption = $this->env('SMTP_ENCRYPTION', 'tls');
            if ($encryption !== '') {
                $mail->SMTPSecure = $encryption;
            }
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($this->env('MAIL_FROM_ADDRESS', 'noreply@pokejob.local'), $this->env('MAIL_FROM_NAME', 'PokéJob'));
        return $mail;
    }

    private function env(string $key, string $default = ''): string {
        return Environment::get($key, $default);
    }
}
