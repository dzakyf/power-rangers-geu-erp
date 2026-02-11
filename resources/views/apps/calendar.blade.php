@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Calendar</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Calendar</span>
            </li>
        </ol>
    </div>
    <!-- End Main Content Header -->

    <!-- Calendar -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-30">
                <div class="card-body">
                    <div class="card-header">
                        <h5 class="card-title">Calendar</h5>
                    </div>

                    <div class="calendar">
                        <div class="calendar__month">
                            <div class="cal-month__previous">
                                <i class="lni lni-angle-double-left"></i>
                            </div>
                            <div class="cal-month__current"></div>
                            <div class="cal-month__next">
                                <i class="lni lni-angle-double-right"></i>
                            </div>
                        </div>
                        <div class="calendar__head">
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                            <div class="cal-head__day"></div>
                        </div>
                        <div class="calendar__body">
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                            <div class="cal-body__week">
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                                <div class="cal-body__day"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Calendar -->

    <!-- Calendar JS -->
    @include('apps.partials.calendar-scripts')
@endsection


