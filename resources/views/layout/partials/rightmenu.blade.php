  <ul class="navbar-nav right-nav ml-auto">


      <!-- <div class="row m-2">
          <i data-feather="inbox" class="icon"></i>
      </div> -->

      <li class="nav-item dropdown profile-nav-item">
          <a href="#" class="btn mr-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View">
              <i data-feather="bell" class="icon"></i>
          </a>
          <div class="dropdown-menu">
              <a class="dropdown-item" style="white-space: normal; width:500px; ">
                  <h6><strong>Notifikasi 1</strong></h6>
                  <p style="
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 0.9rem;
        ">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" style="white-space: normal; width:500px; ">
                  <h6><strong>Notifikasi 1</strong></h6>
                  <p style="
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 0.9rem;
        ">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
              </a>
              <div class="dropdown-divider"></div>
          </div>
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