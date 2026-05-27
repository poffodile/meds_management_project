     <div class="modal fade" id="comingSoon" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
         <div class="modal-dialog">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                     <h4 class="modal-title">Coming Soon </h4>
                 </div>
                 <div class="modal-body">
                     <div class="actionForm">
                         <div class="row">
                             <div class="col-md-12">
                                 <div style="height: 300px; text-align:center;">
                                     <h2
                                         style="font-size: 40px;font-weight: 400; color: #1f88b5; text-transform: uppercase;">
                                         Coming Soon</h2>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>


     <script>
        $(document).on("click", ".openComingSoonModel", function() {
            $("#comingSoon").modal("show");
        });
     </script>
