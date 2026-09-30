<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Helpers\Security;

class AuthController extends Controller {
    public function login(): void {
        if (Auth::check()) {
            $this->redirect('/esg/');
        }
        $this->render('auth/login', [
            'pageTitle' => '使用者登入'
        ], 'none');
    }

    public function doLogin(): void {
        $this->checkCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->render('auth/login', [
                'error'     => '請輸入帳號與密碼',
                'username'  => $username,
                'pageTitle' => '使用者登入'
            ], 'none');
            return;
        }

        $result = Auth::attempt($username, $password);
        if ($result['success']) {
            $this->redirect('/esg/');
        } else {
            $this->render('auth/login', [
                'error'     => $result['message'],
                'username'  => $username,
                'pageTitle' => '使用者登入'
            ], 'none');
        }
    }

    public function logout(): void {
        $this->checkCsrf();
        Auth::logout();
        $this->redirect('/esg/login');
    }
}
