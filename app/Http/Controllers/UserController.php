<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\FileUploadService;
use App\Services\QueryBuilderService;

class UserController extends Controller
{
  protected $uploadService;
  protected $builderService;

  public function __construct(FileUploadService $uploadService, QueryBuilderService $builderService)
  {
    $this->uploadService = $uploadService;
    $this->builderService = $builderService;
  }

  /**
   * Display a list of users.
   *
   * @param Request $request
   * @param User $user
   * @return Response
   */
  public function index(Request $request, User $user): Response
  {
    $options['allowedSorts'] = ['firstname', 'created_at', 'updated_at'];
    $options['conditions'] = [['method' => 'whereNot', 'parameters' => ['id', $request->user()->id]]];

    $users = $this->builderService->query($user, $options);

    return Inertia::render('Account/Users/List', ['users' => $users]);
  }

  /**
   * Display the user creation form.
   *
   * @return Response
   */
  public function create(): Response
  {
    return Inertia::render('Account/Users/Create', []);
  }

  /**
   * Store a new user in the database.
   *
   * @param UserRequest $request
   * @return RedirectResponse
   */
  public function store(UserRequest $request, User $user): RedirectResponse
  {
    $user->fill($request->validated());
    $this->uploadService->uploadAvatar($request, $user);
    $user->save();

    event(new Registered($user));

    return redirect()->route('user.index');
  }

  /**
   * Display a specific user's information for editing.
   *
   * @param User $user
   * @return Response
   */
  public function show(User $user): Response
  {
    return Inertia::render('Account/Users/Edit', ['user' => $user]);
  }

  /**
   * Update a specific user's information.
   *
   * @param UserRequest $request
   * @param User $user
   * @return RedirectResponse
   */
  public function update(UserRequest $request, User $user): RedirectResponse
  {
    $user->fill($request->validated());
    $this->uploadService->uploadAvatar($request, $user);
    $user->update();

    return redirect()->route('user.update', ['user' => $user]);
  }

  /**
   * Delete a specific user from the database.
   *
   * @param Request $request
   * @param User $user
   * @return RedirectResponse
   */
  public function destroy(User $user): RedirectResponse
  {
    $user->delete();
    return redirect()->route('user.index');
  }
}
