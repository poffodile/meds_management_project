@extends('frontEnd.layouts.master')

@section('title','Rota Management')

@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="overview" class="tab-pane active">
                                <div class="row tabs_finance">
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 ">
                                        <a href="{{ url('/rota-dashboard') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row purple-bg">
                                                        <i class="fa fa-home"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Home</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Actions">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row lightRed">
                                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Actions</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- fa-hourglass-end  fa-life-ring  fa-hourglass-half  fa-hourglass-start  fa-clock-o  fa-tachometer  fa-sliders   -->
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/calendar') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row terques-bg">
                                                        <i class="fa fa-calendar-check-o"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Calendar</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/rota') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row bg-purple">
                                                        <i class="fa fa-id-card" aria-hidden="true"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Rotas</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="tab_finance-content">
                                    <div id="Actions" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/absence/type=1') }}">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <svg width="50" height="50" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                            <path fill="white" d="M16 28C15.448 28 15 27.552 15 27V25C15 24.448 15.448 24 16 24C16.552 24 17 24.448 17 25V27C17 27.552 16.552 28 16 28Z"></path>
                                                                            <path fill="white" d="M25.707 24.293C25.888 24.474 26 24.724 26 25C26 25.552 25.552 26 25 26C24.724 26 24.474 25.888 24.293 25.707L22.293 23.707C22.112 23.526 22 23.276 22 23C22 22.448 22.448 22 23 22C23.276 22 23.526 22.112 23.707 22.293L25.707 24.293Z"></path>
                                                                            <path fill="white" d="M7.707 25.707C7.526 25.888 7.276 26 7 26C6.448 26 6 25.552 6 25C6 24.724 6.112 24.474 6.293 24.293L8.293 22.293C8.474 22.112 8.724 22 9 22C9.552 22 10 22.448 10 23C10 23.276 9.888 23.526 9.707 23.707L7.707 25.707V25.707Z"></path>
                                                                            <path fill="white" d="M27 17H25C24.448 17 24 16.552 24 16C24 15.448 24.448 15 25 15H27C27.552 15 28 15.448 28 16C28 16.552 27.552 17 27 17Z"></path>
                                                                            <path fill="white" d="M7 17H5C4.448 17 4 16.552 4 16C4 15.448 4.448 15 5 15H7C7.552 15 8 15.448 8 16C8 16.552 7.552 17 7 17Z"></path>
                                                                            <path fill="white" d="M16 22C12.691 22 10 19.308 10 16C10 12.692 12.691 10 16 10C19.309 10 22 12.692 22 16C22 19.308 19.308 22 16 22ZM16 12C13.794 12 12 13.794 12 16C12 18.206 13.794 20 16 20C18.206 20 20 18.206 20 16C20 13.794 18.206 12 16 12Z"></path>
                                                                            <path fill="white" d="M24.293 6.293C24.474 6.112 24.724 6 25 6C25.552 6 26 6.448 26 7C26 7.276 25.888 7.526 25.707 7.707L23.707 9.707C23.526 9.888 23.276 10 23 10C22.448 10 22 9.552 22 9C22 8.724 22.112 8.474 22.293 8.293L24.293 6.293V6.293Z"></path>
                                                                            <path fill="white" d="M6.293 7.707C6.112 7.526 6 7.276 6 7C6 6.448 6.448 6 7 6C7.276 6 7.526 6.112 7.707 6.293L9.707 8.293C9.888 8.474 10 8.724 10 9C10 9.552 9.552 10 9 10C8.724 10 8.474 9.888 8.293 9.707L6.293 7.707V7.707Z"></path>
                                                                            <path fill="white" d="M16 8C15.448 8 15 7.552 15 7V4.875C15 4.323 15.448 3.875 16 3.875C16.552 3.875 17 4.323 17 4.875V7C17 7.552 16.552 8 16 8Z"></path>
                                                                        </svg>

                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Add Annual Leave</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/absence/type=2') }}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <svg width="50" class="svgColor" height="50" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                            <path fill="white" d="M24.488 16L26.25 14.238C27.375 13.113 28 11.601 28 10C28 8.399 27.375 6.888 26.238 5.763C25.113 4.625 23.601 4 22 4C20.399 4 18.887 4.625 17.762 5.763L16 7.513L14.238 5.75C13.113 4.625 11.601 4 10 4C8.399 4 6.888 4.625 5.763 5.763C4.625 6.888 4 8.401 4 10C4 11.599 4.625 13.113 5.763 14.238L7.513 16L5.75 17.762C4.625 18.887 4 20.399 4 22C4 23.601 4.625 25.113 5.763 26.238C6.888 27.375 8.401 28 10 28C11.599 28 13.113 27.375 14.238 26.238L16 24.476L17.762 26.238C18.899 27.375 20.399 28 22 28C23.601 28 25.113 27.375 26.238 26.238C27.375 25.101 28 23.601 28 22C28 20.399 27.375 18.887 26.238 17.762L24.488 16ZM7.175 12.825C5.612 11.262 5.612 8.725 7.175 7.163C7.925 6.413 8.938 5.988 10 5.988C11.062 5.988 12.075 6.4 12.825 7.163L14.587 8.925L8.925 14.587L7.175 12.825V12.825ZM12.825 24.825C11.262 26.388 8.725 26.388 7.163 24.825C6.413 24.075 5.988 23.063 5.988 22C5.988 20.937 6.4 19.925 7.163 19.175L19.163 7.175C19.938 6.4 20.963 6 21.988 6C23.013 6 24.038 6.388 24.813 7.175C25.563 7.925 25.988 8.937 25.988 10C25.988 11.063 25.575 12.075 24.813 12.825L12.825 24.825ZM24.825 24.825C24.075 25.575 23.063 26 22 26C20.937 26 19.925 25.587 19.175 24.825L17.413 23.063L23.076 17.4L24.838 19.162C26.388 20.725 26.388 23.275 24.826 24.824L24.825 24.825Z"></path>
                                                                            <path fill="white" d="M12 21C12 21.552 11.552 22 11 22C10.448 22 10 21.552 10 21C10 20.448 10.448 20 11 20C11.552 20 12 20.448 12 21Z"></path>
                                                                            <path fill="white" d="M17 16C17 16.552 16.552 17 16 17C15.448 17 15 16.552 15 16C15 15.448 15.448 15 16 15C16.552 15 17 15.448 17 16Z"></path>
                                                                            <path fill="white" d="M22 11C22 11.552 21.552 12 21 12C20.448 12 20 11.552 20 11C20 10.448 20.448 10 21 10C21.552 10 22 10.448 22 11Z"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Add Sickness</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/absence/type=3') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <svg width="50" height="50" class="svgColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                            <path fill="white" d="M26 16C26 12.738 24.425 9.838 22 8V5C22 3.35 20.65 2 19 2H13C11.35 2 10 3.35 10 5V8C7.575 9.825 6 12.725 6 16C6 19.275 7.575 22.163 10 24V27C10 28.65 11.35 30 13 30H19C20.65 30 22 28.65 22 27V24C24.425 22.163 26 19.262 26 16ZM12 5C12 4.45 12.45 4 13 4H19C19.55 4 20 4.45 20 5V6.838C18.775 6.301 17.425 6 16 6C14.575 6 13.225 6.3 12 6.838V5ZM8 16C8 11.588 11.588 8 16 8C20.412 8 24 11.588 24 16C24 20.412 20.413 24 16 24C11.587 24 8 20.413 8 16ZM20 27C20 27.55 19.55 28 19 28H13C12.45 28 12 27.55 12 27V25.163C13.225 25.701 14.575 26 16 26C17.425 26 18.775 25.7 20 25.163V27Z"></path>
                                                                            <path fill="white" d="M19.55 17.163L17 15.463V13C17 12.45 16.55 12 16 12C15.45 12 15 12.45 15 13V16C15 16.35 15.175 16.65 15.45 16.837L18.45 18.837C18.613 18.937 18.8 19 19 19C19.55 19 20 18.55 20 18C20 17.65 19.825 17.35 19.55 17.163Z"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Add Lateness</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/absence/type=4') }}">
                                                                <div>
                                                                    <div class="icon icon6">
                                                                        <svg width="50" height="50" class="svgColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                            <path fill="white" d="M25 10C24.448 10 24 10.448 24 11C24 11.552 24.448 12 25 12C25.551 12 26 12.449 26 13V15C26 15.551 25.551 16 25 16H23.828C23.526 15.149 22.851 14.474 22 14.172V10C22 6.692 19.308 4 16 4C12.692 4 10 6.692 10 10V14.172C9.149 14.474 8.474 15.149 8.172 16H7C6.449 16 6 15.551 6 15V13C6 12.449 6.449 12 7 12C7.551 12 8 11.552 8 11C8 10.448 7.552 10 7 10C5.346 10 4 11.346 4 13V15C4 16.654 5.346 18 7 18H8.172C8.585 19.164 9.696 20 11 20H15V22C12.243 22 10 24.243 10 27C10 27.552 10.448 28 11 28C11.552 28 12 27.552 12 27C12 25.346 13.346 24 15 24V25C15 25.552 15.448 26 16 26C16.552 26 17 25.552 17 25V24C18.654 24 20 25.346 20 27C20 27.552 20.448 28 21 28C21.552 28 22 27.552 22 27C22 24.243 19.757 22 17 22V20H21C22.304 20 23.415 19.164 23.828 18H25C26.654 18 28 16.654 28 15V13C28 11.346 26.654 10 25 10ZM12 10C12 7.794 13.794 6 16 6C18.206 6 20 7.794 20 10V14H12V10ZM21 18H11C10.449 18 10 17.551 10 17C10 16.449 10.449 16 11 16H21C21.551 16 22 16.449 22 17C22 17.551 21.551 18 21 18Z"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Add Other Absences</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>

<script>
    const tabs = document.querySelectorAll('[data-tab-finance-target]');
    const tabContents = document.querySelectorAll('[data-tab-finance-content]');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = document.querySelector(tab.dataset.tabFinanceTarget);

            if (tab.classList.contains('active')) {
                tab.classList.remove('active');
                target.classList.remove('active');
            } else {
                tabContents.forEach(tabContent => tabContent.classList.remove('active'));
                tabs.forEach(tab => tab.classList.remove('active'));

                tab.classList.add('active');
                target.classList.add('active');
            }
        });
    });

    // dropdown show hide
    $(document).ready(function() {
        $(".inner_card_dropdown").click(function(event) {
            event.stopPropagation(); // Prevent click from propagating to document
            var target = $(this).data("target");
            $("." + target).toggle();
        });

        // Click anywhere on the document to close dropdown
        $(document).click(function(event) {
            if (!$(event.target).closest(".inner_card_dropdown, .show_dropdown").length) {
                $(".show_dropdown").hide();
            }
        });
    });
</script>

@endsection