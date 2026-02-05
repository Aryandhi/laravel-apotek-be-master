<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PosLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): Response
    {
        // Check if the request came from POS section
        $referer = $request->headers->get('referer', '');

        if (str_contains($referer, '/pos')) {
            return redirect('/pos/login');
        }

        // Default redirect for admin/other sections
        return redirect('/');
    }
}
