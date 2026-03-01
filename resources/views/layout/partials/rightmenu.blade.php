  <ul class="navbar-nav right-nav ml-auto">


      <!-- <div class="row m-2">
          <i data-feather="inbox" class="icon"></i>
      </div> -->

      <li class="nav-item dropdown profile-nav-item">
          <a href="#" class="btn mr-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View">
              <i data-feather="bell" class="icon"></i>
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

  @push('rightmenu-script')
  <script>
      $(document).ready(function() {
          getNotifications()
      })

      function getNotifications() {
          let notifs = $('#notifMenu')
          notifs.empty()

          $.ajax({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              url: "notification-getall?_token={{ csrf_token() }}",
              type: "GET",
              success: function(data, textStatus, jqXHR) {
                  if (data.length < 1) {
                      notifs.append('<div class="p-3 text-center">No notifications</div>');
                  }

                  data.data.forEach(function(n, i) {
                      notifs.append(`
                <a class="dropdown-item" style="white-space:normal;">
                    <h6><strong>${n.Title || 'Notification'}</strong></h6>
                    <small>${n.Created_At ?? ""}</small>
                    <p style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">${n.Content || ''}</p>
                </a>
            `);
                      if (i < data.length - 1) menu.append('<div class="dropdown-divider"></div>');
                  });
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  alert("Kesalahan Internal! Silakan coba lagi!");

              }
          });
      }
  </script>
  @endpush