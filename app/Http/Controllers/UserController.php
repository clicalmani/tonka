<?php 
namespace App\Http\Controllers;

use Clicalmani\Core\Acme\Controller;
use Clicalmani\Core\Http\RequestInterface;
use \Clicalmani\Core\Resources\ViewInterface;
use \Clicalmani\Core\Http\RedirectInterface;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function index() : ViewInterface
    {
        // Implement the resource listing code

        return view('');
    }

    /**
     * Create the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function create() : ViewInterface
    {
        // Implement the resource creation code

        return view('');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function store(RequestInterface $request)
    {
        // Implement the resource storing code
    }

    /**
     * Show the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function show(RequestInterface $request, int $id) : ViewInterface
    {
        // Implement the resource view code

        return view('');
    }

    /**
     * Edit the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function edit(RequestInterface $request, int $id) : ViewInterface
    {
        // Implement the resource edit code

        return view('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @param  int $id
     * @return \Clicalmani\Foundation\Http\RedirectInterface
     */
    public function update(RequestInterface $request, int $id) : RedirectInterface
    {
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @param int $id 
     * @return \Clicalmani\Foundation\Http\RedirectInterface
     */
    public function destroy(RequestInterface $request, int $id) : RedirectInterface
    {
        return redirect('/');
    }
}