<?php
/**
 * AuthController
 * Handles authentication flows, session security, and access gating.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/Session.php';

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/index.php?action=dashboard');
            exit;
        }
        require dirname(__DIR__) . '/views/auth/login.php';
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('danger', 'Security validation (CSRF) failed. Please try again.');
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            Session::setFlash('warning', 'Please provide both email address and password.');
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        if (Auth::attempt($email, $password)) {
            Session::setFlash('success', 'Authentication successful. Welcome, ' . htmlspecialchars(Session::get('user_name')));
            header('Location: ' . BASE_URL . '/index.php?action=dashboard');
            exit;
        } else {
            Session::setFlash('danger', 'Invalid credentials or inactive account.');
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    public function logout(): void {
        Auth::logout();
        Session::setFlash('info', 'You have been securely signed out.');
        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }
}
