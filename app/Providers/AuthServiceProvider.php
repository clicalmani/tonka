<?php
namespace App\Providers;

use App\Models\AuthAccess;
use Clicalmani\Core\Auth\Authenticate;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Support\Facades\DB;

class AuthServiceProvider extends Authenticate
{
	protected string $userModel = \App\Models\User::class;

	public function getConnectedUserID(?RequestInterface $request) : mixed
	{
		if ($user_id = $request->session('user:id')->get()) {
			return (int)$user_id;
		}
		
		return null;
	}

    /**
	 * Authenticate user or renew user authentication.
	 * 
	 * @return void
	 */
	public function authenticate() : void
	{
		if ($this->user_id) {
			DB::table('auth_access')
				->where('user_id = :user', ['user' => (int) $this->user_id])
				->insert([
					['user_id' => $this->user_id, 'token' => token($this->user_id)]
				])->onDuplicateKeyUpdate(['user_id'])->query();
		}
	}

	/**
	 * Is user authenticated
	 * 
	 * @return bool
	 */
	public function isAuthenticated() : bool
	{
		return !!DB::table('auth_access')->where('user_id = :user_id', ['user_id' => $this->user_id])->get('token')->first();
	}

	/**
	 * Is user online
	 * 
	 * @return bool
	 */
	public function isOnline() : bool
	{
		$auth = DB::table('auth_access')->where('user_id = :user_id', 'AND', ['user_id' => $this->user_id])->get('token')->first();
		if ($auth && verify_token((string)$auth->token)) return true;
		return false;
	}

	public function destroy()
	{
		if ($this->user_id) {
			AuthAccess::where('user_id = ?', [$this->user_id])->first()?->delete();
		}
	}
}
