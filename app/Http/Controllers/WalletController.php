<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\Wallet\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Public, token-based wallet enrollment. A member scans their personal "get
 * your card" QR (which encodes /wallet/card/{token}); the landing page offers
 * the right wallet for their phone. The token is random + unguessable, so no
 * login is needed.
 */
class WalletController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    /** Platform-detecting landing page with the available "Add to wallet" buttons. */
    public function landing(Request $request, string $token): View
    {
        $member = $this->memberFor($token);
        $ua = (string) $request->userAgent();

        return view('wallet.card', [
            'member' => $member,
            'token' => $token,
            'apple' => $this->wallet->appleEnabled(),
            'google' => $this->wallet->googleEnabled(),
            'isIos' => (bool) preg_match('/iPhone|iPad|iPod/i', $ua),
            'isAndroid' => (bool) preg_match('/Android/i', $ua),
        ]);
    }

    /** Download the member's Apple Wallet pass (.pkpass). */
    public function apple(string $token): Response
    {
        $member = $this->memberFor($token);
        abort_unless($this->wallet->appleEnabled(), 404);

        $filename = Str::slug($member->name ?: 'member').'-'.$member->code.'.pkpass';

        return response($this->wallet->applePass($member), 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Redirect to the member's "Add to Google Wallet" link. */
    public function google(string $token): RedirectResponse
    {
        $member = $this->memberFor($token);
        abort_unless($this->wallet->googleEnabled(), 404);

        return redirect()->away($this->wallet->googleSaveUrl($member));
    }

    private function memberFor(string $token): Member
    {
        abort_unless($this->wallet->appleEnabled() || $this->wallet->googleEnabled(), 404);

        return Member::where('wallet_token', $token)->firstOrFail();
    }
}
