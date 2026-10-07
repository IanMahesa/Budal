<?php

namespace App\Http\Controllers;

class QrCodeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('qrcode.index', compact('user'));
    }
}
