<?php
namespace App\Providers;

use Clicalmani\Foundation\Support\Facades\DB;
use Clicalmani\Foundation\Auth\Authenticate;
use Clicalmani\Foundation\Http\RequestInterface;

class AuthServiceProvider extends Authenticate
{
	protected string $userModel = \App\Models\User::class;

	public function getConnectedUserID(?RequestInterface $request) : mixed
	{
		if ($payload = verify_token($request->bearerToken())) {
			return (int) json_decode($payload->jti);
		}
		
		return null;
	}

	public function boot(): void
	{
		$this->serialize(fn() => $this->user->profile);
	}

    /**
	 * Authenticate user or renew user authentication.
	 * 
	 * @return void
	 */
	public function authenticate() : void
	{
		DB::table('auth_access')
			->where('user_id = :user', 'AND', ['user' => (int) $this->user_id])
			->insert([
				['user_id' => $this->user_id, 'token' => $this->auth->generateToken()]
			], true);
	}

	/**
	 * Is user online
	 * 
	 * @return bool
	 */
	public function isOnline() : bool
	{
		$auth = DB::table('auth_access')->where('user_id = :user_id', 'AND', ['user_id' => $this->user_id])->get('token')->first();
		if ($auth && verify_token($auth['token'])) return true;

		return false;
	}
}
