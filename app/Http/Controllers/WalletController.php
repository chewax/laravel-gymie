<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    /** Download a member's Apple Wallet pass (.pkpass). */
    public function apple(Member $member): Response
    {
        abort_unless($this->wallet->appleEnabled(), 404);

        $contents = $this->wallet->applePass($member);
        $filename = Str::slug($member->name ?: 'member').'-'.$member->code.'.pkpass';

        return response($contents, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Redirect to the member's "Add to Google Wallet" link. */
    public function google(Member $member): RedirectResponse
    {
        abort_unless($this->wallet->googleEnabled(), 404);

        return redirect()->away($this->wallet->googleSaveUrl($member));
    }
}
