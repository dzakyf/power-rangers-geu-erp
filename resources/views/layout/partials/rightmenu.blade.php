  <ul class="navbar-nav right-nav ml-auto">


      <!-- <div class="row m-2">
          <i data-feather="inbox" class="icon"></i>
      </div> -->

      <li class="nav-item dropdown profile-nav-item">

          <a href="#" class="btn mr-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View">
              <div class="row notif-icon-section">
                  <i data-feather="bell" class="icon"></i>
              </div>
          </a>

          <div class="dropdown-menu" id="notifMenu" style="width: 500px;"></div>

      </li>

      <!-- Profile dropdown -->
      <li class="nav-item dropdown profile-nav-item">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <div class="menu-profile">
                  <span>{{Auth::user()->username}}</span> / <span>{{Auth::user()->Department}}</span> / <span> {{Auth::user()->Location}} </span>&nbsp;&nbsp;
                  <img src="{{ asset('assets/images/profile.jpg') }}" alt="Profile Image" class="rounded-circle">
              </div>
          </a>
          <div class="dropdown-menu">
              <a class="dropdown-item" href="my-profile">
                  <i data-feather="user" class="icon"></i>
                  Profile
              </a>
              <a class="dropdown-item" href="change-password">
                  <i data-feather="key" class="icon"></i>
                  Change Password
              </a>
              <a class="dropdown-item" href="privilege-information">
                  <i data-feather="help-circle" class="icon"></i>
                  Privilege Information
              </a>
              <a class="dropdown-item" href="{{ route('logout') }}"
                  onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                  <i data-feather="log-out" class="icon"></i>
                  Logout
              </a>
          </div>
      </li>
  </ul>
