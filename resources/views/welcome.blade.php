<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Token Print</title>

    <!-- Fonts -->
    <link href="{{ asset('public/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

    <link rel="stylesheet" href="{{ asset('public/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/css/mobile.css') }}" />
    <!-- Styles -->



</head>

<body class="antialiased bg-light" onload="initClock()">
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Custom Print Information</h5>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customPrint') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" class="form-control" name="name">
                            </div>
                            <div class="form-group mt-3">
                                <label for="">Employee ID</label>
                                <input type="text" class="form-control" name="emp_id">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Print</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row mt-16">
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Print Details
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-info">
                                    <th class="bg-info">Meal Type</th>
                                    <th class="bg-info">Start Time</th>
                                    <th class="bg-info">End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $meals = DB::connection('oracle')->table('meal_time_all')->get();
                                @endphp
                                @foreach ($meals as $meal)
                                    <tr>
                                        <td>{{ $meal->meal_type }}</td>
                                        <td>{{ date('h:i:s a', strtotime($meal->time_start)) }}</td>
                                        <td>{{ date('h:i:s a', strtotime($meal->time_end)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- <table class="table table-bordered">
                            <thead>
                                <tr class="bg-warning">
                                    <th class="bg-warning">Current Meal Type</th>
                                    <th class="bg-warning">Current Token Serial</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $meals = DB::connection('oracle')->table('meal_time_all')->get();
                                    $mealType = mealType();
                                @endphp
                                <tr id="updated_meal">
                                    <td>{{ mealType() }}</td>
                                    <td>{{ token_serial($mealType, date('m-d-Y')) - 1 }}</td>
                                </tr>
                            </tbody>
                        </table> --}}
                        {{-- Start Time : {{ date('h:i A',strtotime(config('print_config')['start_time'])) }} <br>
                            End Time : {{ date('h:i A',strtotime(config('print_config')['end_time'])) }} <br> --}}
                        {{-- Printing Days : @foreach (config('print_config')['days_name'] as $key => $day)
                                {{ $day }}
                                {{ $key<count(config('print_config')['days_name'])-1?',':'' }}
                            @endforeach <br> --}}
                    </div>
                    @php
                        $meals = DB::connection('oracle')->table('meal_time_all')->get();
                        $mealType = mealType();
                    @endphp
                    <div class="card-body mt-0 pt-0 pb-4">
                        <div class="row mt-0 py-0">
                            <div class="col-5 card-one mx-auto">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Current Meal Type</h4>
                                    </div>
                                    <div class="col-12">
                                        <h3 id="current_meal_type">{{ mealType() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5 card-two  mx-auto">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Current Token Serial</h4>
                                    </div>
                                    <div class="col-12">
                                        <h3 id="current_token_serial">{{ token_serial($mealType, date('m-d-Y')) - 1 }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0 my-2">
                        <div class="row">
                            <div class="col-6">
                                <div class="clock bg-dark mt-0">
                                    <div class="hour">
                                        <div class="hor" id="hor">

                                        </div>
                                    </div>
                                    <div class="minutes">
                                        <div class="mn" id="mn">

                                        </div>
                                    </div>
                                    <div class="seconds">
                                        <div class="sc" id="sc">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="datetime">
                                    <div class="date">
                                        <span id="day">Day</span>,
                                        <span id="month">Month</span>
                                        <span id="num">00</span>,
                                        <span id="year">Year</span>
                                    </div>
                                    <div class="time">
                                        <span id="hour">00</span>:
                                        <span id="min">00</span>:
                                        <span id="sec">00</span>
                                        <span id="period">AM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Analog Clock -->


                    </div>
                    <div class="card-body mt-5">
                        @if (session()->has('type'))
                            <div class="alert alert-{{ session()->get('type') }} alert-dismissible fade show"
                                role="alert">
                                <strong>{{ session()->get('message') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- <a href="{{ route('startSchedule') }}" class="btn btn-success">Turn On Schedule</a>
                            <a href="" class="btn btn-danger">Turn Off Schedule</a>
                            <a href="" class="btn btn-warning">Restart Schedule</a> --}}
                        <button type="button" class="btn btn-primary mx-2" style="float: right" data-bs-toggle="modal" data-bs-target="#exampleModal">Custom
                            Print</button>
                        <a href="{{ url('test-print') }}" class="btn btn-info " style="float: right">Test Print</a>
                    </div>
                </div>
            </div>
            {{-- <div class="col-lg-6 mx-auto">
                    <div class="card" style="height: 300px;">
                        <div class="card-header text-center">
                            Print Config Update
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('print_config') }}" class="form">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-lg-6">
                                        <label for="">Start Time</label>
                                        <input type="time" name="start_time" class="form-control">
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <label for="">End Time</label>
                                        <input type="time" name="end_time" class="form-control">
                                    </div>
                                    <div class="form-group col-lg-12">
                                        <label for="">Select Days</label>
                                        <select name="days[]" id="" class="js-example-basic-single" style="width:100%" multiple>
                                            <option value="6">Saturday</option>
                                            <option value="0">Sunday</option>
                                            <option value="1">Monday</option>
                                            <option value="2">Tuesday</option>
                                            <option value="3">Wednesday</option>
                                            <option value="4">Thursday</option>
                                            <option value="5">Friday</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-5">
                                        <button type="submit" class="btn btn-primary" >Update</button>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div> --}}
        </div>
    </div>
    <script src="{{ asset('public/jquery/jquery-3.7.1.min.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script src="{{ asset('public/bootstrap/js/bootstrap.bundle.min.js') }}"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="{{ asset('public/js/clock.js') }}"></script>
    <script src="{{ asset('public/js/digital.js') }}"></script>
    <script>
        function getCurrentMealInfo() {
            $.ajax({
                type: "GET",
                url: "get-current-meal-info",
                success: function(data) {
                    $('#current_token_serial').empty().append(
                        `${data.current_token_serial}`);

                    $('#current_meal_type').empty().append(
                        `${data.meal_type}`);
                },
                error: function(err) {

                }
            })
        }
        $(document).ready(function() {
            setInterval(getCurrentMealInfo, 3000);
        });
    </script>

</body>

</html>
