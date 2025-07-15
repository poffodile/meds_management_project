<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="author" content="ThemeBucket">
  <link rel="shortcut icon" href="images/favicon.png">
  <title>{{ PROJECT_NAME }}@yield('title','')</title>
  <link rel='stylesheet' href='https://cdn.form.io/formiojs/formio.full.min.css'>
  <!--Core CSS -->

  <link href="{{ url('public/backEnd/css/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ url('public/backEnd/css/bootstrap/bootstrap-reset.css') }}" rel="stylesheet">

  <link href="{{ url('public/backEnd/js/jquery-ui-1.10.1.custom.min.css') }}" rel="stylesheet">
  <link href="{{ url('public/backEnd/js/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet">
  <link href="{{ url('public/backEnd/css/clndr.css') }}" rel="stylesheet">
  <!--clock css-->
  <link href="{{ url('public/backEnd/css/style.css') }}" rel="stylesheet">
  <!--Morris Chart CSS -->
  <link rel="stylesheet" href="{{ url('public/backEnd/js/morris.css') }}">

  <!-- Users table CSS Files -->

  <link href="{{ url('public/backEnd/css/font-awesome.css') }}" rel="stylesheet" />
  <link href="{{ url('public/backEnd/css/DT_bootstrap.css') }}" rel="stylesheet" />
  <link href="{{ url('public/backEnd/css/margins-min.css') }}" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="{{ url('public/backEnd/css/style.css') }}" rel="stylesheet">
  <link href="{{ url('public/backEnd/css/style-responsive.css') }}" rel="stylesheet" />
  <link href="{{ url('public/backEnd/js/bootstrap-datepicker/css/datepicker.css') }}" rel="stylesheet" type="text/css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script> -->
  <link rel="stylesheet" href="{{ url('public/backEnd/css/developer.css') }}" />

  <script src="{{ url('public/backEnd/js/jquery.js') }}"></script>
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script> -->
  <script src="{{ url('public/backEnd/js/slimscroll.js') }}"></script>

  <script src="{{ url('public/frontEnd/js/autosize.js') }}"></script>

  <!--dynamic table-->
  <link href="{{ url('public/backEnd/css/demo_page.css') }}" rel="stylesheet" />
  <link href="{{ url('public/backEnd/css/demo_table.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

  <style>
    .loader-inner {
      display: none;
    }

    .loader {
      width: 80px;
      height: 80px;
      top: 45%;
      left: 60%;
    }
  </style>


</head>

<body class="page-header-fixed page-quick-sidebar-over-content ">
  <!-- <div class="loader-box loader"> full-width
      <div class="loader-inner"></div>
  </div> -->
  <div class="loader">
    <div class="loader-inner"></div>
  </div>


  @include('backEnd.common.header')
  <div class="clearfix"></div>

  <!-- BEGIN CONTAINER -->
  <div class="page-container">
    @include('backEnd.common.sidebar')

    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
      @yield('content')
    </div>
    <!-- END QUICK SIDEBAR -->
  </div>
  <!-- END CONTAINER -->


  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <!-- VALIDATION FILES -->
  <script type="text/javascript" src="{{ url('public/backEnd/js/validation/formValidation.min.js') }}"></script>
  <script type="text/javascript" src="{{ url('public/backEnd/js/validation/bootstrap.js') }}"></script>
  <script type="text/javascript" src="{{ url('public/backEnd/js/validation/validations_rule.js') }}"></script>


  <script type="text/javascript" src="{{ url('public/backEnd/js/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>

  <!--clock init-->
  <script src="{{ url('public/backEnd/js/css3clock.js') }}"></script>

  <!-- Placed js at the end of the document so the pages load faster -->
  <!--common script init for all pages-->
  <script src="{{ url('public/backEnd/js/scripts.js') }}"></script>
  <script src="{{ url('public/backEnd/js/common.js') }}"></script>

  <!--Core js-->
  <!-- <script src="{{ url('public/backEnd/js/bootstrap.min.js') }}"></script> -->
  <script class="include" type="text/javascript" src="{{ url('public/backEnd/js/jquery.dcjqaccordion.2.7.js') }}"></script>
  <script src="{{ url('public/backEnd/js/jquery.scrollTo.min.js') }}"></script>
  <script src="{{ url('public/backEnd/js/jquery.slimscroll.js') }}"></script>
  <script src="{{ url('public/backEnd/js/jquery.nicescroll.js') }}"></script>
  <script src="{{ url('public/backEnd/js/jquery.dataTables.js') }}"></script>
  <script src="{{ url('public/backEnd/js/DT_bootstrap.js') }}"></script>
  <script src="{{ url('public/backEnd/js/table-editable.js') }}"></script>

  <!--dynamic table-->
  <script type="text/javascript" language="javascript" src="{{ url('public/backEnd/js/jquery.dataTables.js') }}"></script>
  <script type="text/javascript" language="javascript" src="{{ url('public/backEnd/js/dynamic_table_init.js') }}"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  


  <!--Easy Pie Chart-->
  <!-- <script src="{{ url('public/backEnd/js/jquery.easypiechart.js') }}"></script> -->
  <!--Sparkline Chart-->
  <!-- <script src="{{ url('public/backEnd/js/jquery.sparkline.js') }}"></script> -->
  <!--Morris Chart-->
  <!-- <script src="{{ url('public/backEnd/js/morris.js') }}"></script>
<script src="{{ url('public/backEnd/js/raphael-min.js') }}"></script> -->
  <!--jQuery Flot Chart-->
  <!-- <script src="{{ url('public/backEnd/js/jquery.flot.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.flot.resize.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.flot.pie.resize.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.flot.animator.min.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.flot.growraf.js') }}"></script>
<script src="{{ url('public/backEnd/js/dashboard.js') }}"></script>
<script src="{{ url('public/backEnd/js/jquery.customSelect.min.js') }}" ></script>
 -->

  @if(Session::has('image_id'))
  <input type="hidden" id="image_id" value="{{ Session::get('image_id')}}">
  @endif


  <script src="https://cdn.form.io/formiojs/formio.full.min.js"></script>
  <script type='text/javascript'>
    // alert(data);

    var datafrm = <?= (isset($form->pattern)) ? $form->pattern : "1" ?>;
    // console.log(datafrm);
    // if(datafrm== ""){
    //   const setdatapattern="{}";
    // }else{
    const setdatapattern = {
      "components": datafrm
    };
    // }

    //const setdatapatterns={"components": data1};
    //console.log(setdatapattern);
    //console.log(setdatapatterns);
    //alert(typeof data2);
    // JSON.parse(data);
    Formio.builder(document.getElementById('builder'), (datafrm == 1) ? {} : setdatapattern, {}).then(function(builder) {
      builder.on('saveComponent', function() {
        // console.log(JSON.stringify(builder.schema.components));
        $('#setformdata').val(JSON.stringify(builder.schema.components));
        //console.log(data34);
      });
    });
    // window.location.hash
    // var url = window.location.pathname;
    //  var id = url.substring(url.lastIndexOf('/') + 1);
    //var id = window.location.hash;
  </script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    })
    setTimeout(function() {
      $("#group-basic").removeClass("show collapse");
    }, 200);

    function PrintDiv() {
      // var imagelocalpath = "<?php echo adminImgPath ?>";
      var imagelocalpath = "{{ url('/public/images/admin') }}";
      let imagewithser = $('#image_id').val();
      console.log(imagelocalpath);
      var finalpath = imagelocalpath + '/' + imagewithser;
      console.log(finalpath);
      $('.formcomponents').css('display', 'none');
      $('.formarea').css('flex', "140%");
      $('.formarea').css('width', "140%");
      var divContents = document.getElementById("builder").innerHTML;
      //console.log(divContents);
      var printWindow = window.open('', '', 'height=600,width=800');
      printWindow.document.write('<html><head><title>Print DIV Content</title> ');
      printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">');
      printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">');
      printWindow.document.write('<link rel="stylesheet" href="https://cdn.form.io/formiojs/formio.full.min.css">');
      printWindow.document.write("<link href='{{ url('public/backEnd/css/amarjeet.css ')}}' rel='stylesheet' type='text/css' >");
      printWindow.document.write('<link href="{{ url("public/backEnd/css/pdfstyle.css")}}" rel="stylesheet" type="text/css" >');
      printWindow.document.write('</head><body ><div class="masterprintmainareaadmin">');
      printWindow.document.write('<div class="header mb-2">');
      printWindow.document.write('<img src="' + finalpath + '" style="height:80px;">');
      printWindow.document.write("<img src='{{url('/public/images/scits.png ')}}' style='float:right;height:80px;'>");
      printWindow.document.write('</div>');
      printWindow.document.write(divContents);
      printWindow.document.write('<div class="footer">');
      printWindow.document.write('<div class="footer-section-area">');
      printWindow.document.write("© {{ date('Y') }} Omega Care Group (SCITS). All Rights Reserved | www.socialcareitsolutions.co.uk");
      printWindow.document.write('</div>');
      printWindow.document.write('</div>');
      printWindow.document.write('</div></body></html>');

      setTimeout(() => {
        printWindow.document.close();
        printWindow.print();
      }, 500);

      $('.formcomponents').css('display', 'block');
      $('.formarea').css('flex', "");
      $('.formarea').css('width', "");
    }
  </script>
</body>

</html>