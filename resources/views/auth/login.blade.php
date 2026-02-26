@extends('layout.mainlayout-two')

@section('content-wrapper')
    <!-- Login Area -->
    <div class="auth-main-content auth-bg-image">
        <div class="d-table">
            <div class="d-tablecell">
                <div class="auth-box">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-left-content">
                                    <img src="{{ asset('large-logo.png') }}" width="300px" alt="Logo">
                               
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-content">
                                <h1 class="heading">Log In</h1>
                                <form method="POST" action="login">
								@csrf
                                    <div class="form-group">
                                        <label class="form-label">Username</label>
                                        <input type="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" id="username" name="username" required>
										  @if ($errors->has('username'))
											<span class="invalid-feedback" role="alert">
												<strong>{{ $errors->first('username') }}</strong>
											</span>
										@endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Password</label>
                                        <input type="password"  class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" required>
										 @if ($errors->has('password'))
											<span class="invalid-feedback" role="alert">
												<strong>{{ $errors->first('password') }}</strong>
											</span>
										@endif
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Log In</button>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Login Area -->
@endsection
