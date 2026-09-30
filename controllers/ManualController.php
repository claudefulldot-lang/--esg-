<?php
namespace App\Controllers;

use App\Auth;
use App\Controller;

class ManualController extends Controller {
    public function index(): void {
        Auth::requireLogin();

        $this->render('manual/index', [
            'pageTitle' => '系統操作手冊與問題排解',
            'activeNav' => 'manual',
            'extraStyles' => ['/esg/assets/css/manual.css'],
            'extraScripts' => ['/esg/assets/js/manual.js'],
        ]);
    }
}
