<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>News & Announcement - Mobile</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 75px;
            color: #1e293b;
        }
        .header-announcement {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 20px 16px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
        }
        .feed-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.15s ease;
        }
        .feed-card:active {
            transform: scale(0.985);
        }
        .feed-img-thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .category-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        .category-pill.news { background-color: #eff6ff; color: #2563eb; }
        .category-pill.announcement { background-color: #fef2f2; color: #dc2626; }
        .category-pill.policy { background-color: #f0fdf4; color: #16a34a; }
        .category-pill.event { background-color: #fdf4ff; color: #9333ea; }
        .pinned-tag {
            background: #fef3c7;
            color: #b45309;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
        }
        .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 4px 0 10px;
            -webkit-overflow-scrolling: touch;
        }
        .category-scroll::-webkit-scrollbar {
            display: none;
        }
        .chip-filter {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            background: #fff;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            display: inline-block;
        }
        .chip-filter.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }
        .fab-btn {
            position: fixed;
            bottom: 80px;
            right: 18px;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #1e3a8a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 16px rgba(30, 58, 138, 0.4);
            z-index: 99;
            text-decoration: none;
        }
        .nav-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 100;
        }
        .nav-bottom-item {
            color: #64748b;
            text-decoration: none;
            text-align: center;
            font-size: 0.72rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .nav-bottom-item.active {
            color: #1e3a8a;
            font-weight: 700;
        }
        .nav-bottom-item i {
            font-size: 1.15rem;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header-announcement">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('employee.mobile.my-profile') }}" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <h5 class="fw-bold mb-0 text-white">News & Updates</h5>
            </div>
            <div>
                @if($isHcga)
                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill small"><i class="fa fa-shield-alt me-1"></i>HCGA Admin</span>
                @else
                    <span class="badge bg-white text-dark px-2 py-1 rounded-pill small"><i class="fa fa-user me-1"></i>Employee</span>
                @endif
            </div>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('announcement.mobile.index') }}" method="GET" class="mb-2">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white border-0 text-muted"><i class="fa fa-search"></i></span>
                <input type="text" name="q" class="form-control border-0 ps-0 shadow-none" placeholder="Cari berita atau pengumuman..." value="{{ $searchQuery }}">
                @if($searchQuery)
                    <a href="{{ route('announcement.mobile.index') }}" class="input-group-text bg-white border-0 text-muted"><i class="fa fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Container -->
    <div class="container px-3 mt-3">

        <!-- Role Tab switcher for HCGA -->
        @if($isHcga)
            <div class="btn-group w-100 mb-3 shadow-sm rounded-pill p-1 bg-white border">
                <a href="{{ route('announcement.mobile.index', ['tab' => 'all', 'category' => $currentCat]) }}" class="btn btn-sm rounded-pill {{ $currentTab === 'all' ? 'btn-primary' : 'btn-light text-muted' }}">
                    Semua Berita
                </a>
                <a href="{{ route('announcement.mobile.index', ['tab' => 'my', 'category' => $currentCat]) }}" class="btn btn-sm rounded-pill {{ $currentTab === 'my' ? 'btn-primary' : 'btn-light text-muted' }}">
                    Buatan Saya
                </a>
                <a href="{{ route('announcement.mobile.index', ['tab' => 'draft', 'category' => $currentCat]) }}" class="btn btn-sm rounded-pill {{ $currentTab === 'draft' ? 'btn-primary' : 'btn-light text-muted' }}">
                    Draft
                </a>
            </div>
        @endif

        <!-- Category Horizontal Scroll -->
        <div class="category-scroll">
            <a href="{{ route('announcement.mobile.index', array_merge(request()->except('category', 'page'), ['category' => 'ALL'])) }}" class="chip-filter {{ $currentCat === 'ALL' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('announcement.mobile.index', array_merge(request()->except('category', 'page'), ['category' => 'ANNOUNCEMENT'])) }}" class="chip-filter {{ $currentCat === 'ANNOUNCEMENT' ? 'active' : '' }}">Announcement</a>
            <a href="{{ route('announcement.mobile.index', array_merge(request()->except('category', 'page'), ['category' => 'NEWS'])) }}" class="chip-filter {{ $currentCat === 'NEWS' ? 'active' : '' }}">News</a>
            <a href="{{ route('announcement.mobile.index', array_merge(request()->except('category', 'page'), ['category' => 'POLICY'])) }}" class="chip-filter {{ $currentCat === 'POLICY' ? 'active' : '' }}">Policy</a>
            <a href="{{ route('announcement.mobile.index', array_merge(request()->except('category', 'page'), ['category' => 'EVENT'])) }}" class="chip-filter {{ $currentCat === 'EVENT' ? 'active' : '' }}">Event</a>
        </div>

        <!-- News Feed List -->
        @if($announcements->count() > 0)
            <div class="mt-2">
                @foreach($announcements as $item)
                    @php
                        $catClass = strtolower($item->category ?? 'announcement');
                    @endphp
                    <a href="{{ route('announcement.mobile.detail', $item->id) }}" class="feed-card">
                        @if($item->thumbnail)
                            <img src="{{ asset($item->thumbnail) }}" alt="{{ $item->title }}" class="feed-img-thumb" loading="lazy">
                        @endif
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="category-pill {{ $catClass }}">{{ $item->category ?? 'ANNOUNCEMENT' }}</span>
                                    @if($item->is_pinned)
                                        <span class="pinned-tag"><i class="fa fa-thumbtack me-1"></i>Pinned</span>
                                    @endif
                                </div>
                                <span class="small text-muted" style="font-size: 0.72rem;">
                                    <i class="fa fa-clock me-1"></i>{{ date('d M Y', strtotime($item->created_date)) }}
                                </span>
                            </div>

                            <h6 class="fw-bold text-dark mb-1" style="line-height: 1.4;">
                                {{ $item->title }}
                            </h6>

                            <p class="text-secondary small mb-2 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.8rem; line-height: 1.4;">
                                {{ $item->summary ?: strip_tags($item->content) }}
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2 small text-muted">
                                <div class="d-flex align-items-center gap-2">
                                    <span><i class="fa fa-user me-1"></i>{{ $item->created_by }}</span>
                                    @if($item->attachment_file)
                                        <span><i class="fa fa-paperclip text-primary ms-1"></i> Lampiran</span>
                                    @endif
                                </div>
                                <div>
                                    @if(!$item->is_final)
                                        <span class="badge bg-secondary text-white">Draft</span>
                                    @else
                                        <span><i class="fa fa-eye me-1"></i>{{ $item->view_count ?? 0 }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $announcements->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5 bg-white rounded-3 border mt-2">
                <i class="fa fa-newspaper fa-3x text-muted mb-3 opacity-50"></i>
                <h6 class="fw-bold text-dark">Belum ada pengumuman</h6>
                <p class="text-muted small px-4 mb-3">Tidak ada berita atau pengumuman yang sesuai dengan filter pencarian Anda.</p>
                @if($isHcga)
                    <a href="{{ route('announcement.mobile.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fa fa-plus-circle me-1"></i> Buat Berita Pertama
                    </a>
                @endif
            </div>
        @endif

    </div>

    <!-- HCGA / Admin Floating Action Button for Create -->
    @if($isHcga)
        <a href="{{ route('announcement.mobile.create') }}" class="fab-btn shadow" title="Buat Pengumuman Baru">
            <i class="fa fa-plus"></i>
        </a>
    @endif

    <!-- Bottom Navigation Bar -->
    <div class="nav-bottom-bar">
        <a href="{{ route('employee.mobile.my-profile') }}" class="nav-bottom-item">
            <i class="fa fa-id-card"></i>
            <span>Profil</span>
        </a>
        <a href="{{ route('announcement.mobile.index') }}" class="nav-bottom-item active">
            <i class="fa fa-newspaper"></i>
            <span>Berita</span>
        </a>
        <a href="{{ route('employee.mobile.directory') }}" class="nav-bottom-item">
            <i class="fa fa-address-book"></i>
            <span>Direktori</span>
        </a>
        <a href="{{ route('attendance.mobile.index') }}" class="nav-bottom-item">
            <i class="fa fa-fingerprint"></i>
            <span>Absensi</span>
        </a>
    </div>

</body>
</html>
