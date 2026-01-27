@extends('layouts.guest')

@section('content')
    <div class="bg-light min-vh-100 d-flex flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mb-4 mx-4">
                        <div class="card-body p-4">
                            <h1>Register</h1>
                            <p class="text-medium-emphasis">Create your account</p>
                            <form action="{{ route('register') }}" method="POST">
                                @csrf
                                <div class="input-group mb-3"><span class="input-group-text">
                                        <i class="icon cil-user"></i></span>
                                    <input class="form-control" type="text" name="name" placeholder="Username"
                                        value="{{ old('name') }}" required>
                                </div>
                                @error('name')<div class="text-danger mb-3">{{ $message }}</div>@enderror

                                <div class="input-group mb-3"><span class="input-group-text">
                                        <i class="icon cil-envelope-open"></i></span>
                                    <input class="form-control" type="text" name="email" placeholder="Email"
                                        value="{{ old('email') }}" required>
                                </div>
                                @error('email')<div class="text-danger mb-3">{{ $message }}</div>@enderror

                                <div class="input-group mb-3"><span class="input-group-text">
                                        <i class="icon cil-building"></i></span>
                                    <input class="form-control" type="text" name="workspace_name"
                                        placeholder="Workspace Name (Optional)" value="{{ old('workspace_name') }}">
                                </div>

                                <div class="input-group mb-3"><span class="input-group-text">
                                        <i class="icon cil-lock-locked"></i></span>
                                    <input class="form-control" type="password" name="password" placeholder="Password"
                                        required>
                                </div>
                                @error('password')<div class="text-danger mb-3">{{ $message }}</div>@enderror

                                <div class="input-group mb-4"><span class="input-group-text">
                                        <i class="icon cil-lock-locked"></i></span>
                                    <input class="form-control" type="password" name="password_confirmation"
                                        placeholder="Repeat password" required>
                                </div>

                                <button class="btn btn-block btn-success" type="submit">Create Account</button>
                            </form>
                        </div>
                        <div class="card-footer p-4">
                            <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection