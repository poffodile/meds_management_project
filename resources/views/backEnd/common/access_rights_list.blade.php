<style>
  .border_bottom {
    box-shadow: rgba(33, 35, 38, 0.1) 0px 10px 10px -10px;
    margin-bottom: 20px;
    padding-bottom: 20px;
  }

  .spacing-top {
    padding: 3px 0;
  }
</style>
<div class="label_selct">
  <div class="checkbox">
    <label>
      <input name="access_id[]" class="select_all" value="172"  type="checkbox">
      <strong>[ Select All ]</strong>
    </label>
  </div>
</div>
<div class="row">
  <div class="col-sm-12 border_bottom">
    <h4>Dashboard</h4>
    <?php foreach ($dashboard_rights as $value) { ?>
      <div class="checkbox">
        <div class="spacing-top">
          <label><input type="checkbox" name="access_id[]" value="{{ $value['id'] }}" {{ (in_array($value['id'],$available_rights)) ? 'checked':'' }}>{{ ucfirst($value['module_name']) }}</label>
        </div>
      </div>
    <?php } ?>
  </div>
</div>

<div class="row">
  <?php foreach ($access_rights as $management) { ?>
    <div class="col-sm-12 border_bottom">
      @if(count($management['module_list']) > 0)
      <h4>{{ ucfirst($management['name']) }}</h4>
      @endif
      <div class="row d-flex" style="flex-wrap: wrap;">
        <?php foreach ($management['module_list'] as $module) { ?>
          <div class="col-sm-4">
            <div class="checkbox">
              <!-- name="module_code[]" value="{{ $module['module_code'] }}" -->
              <?php
              $chekd_checkbx = 0;
              $total_checkbx = 0;
              foreach ($module['sub_modules'] as $sub_modules) {
                if (in_array($sub_modules['id'], $available_rights)) {
                  $chekd_checkbx++;
                }
                $total_checkbx++;
              }

              if ($total_checkbx == $chekd_checkbx) {
                $selected = 'y';
              } else {
                $selected = 'n';
              }

              ?>


              <label><input type="checkbox" class="acc_heading_chkbox" {{ ($selected == 'y') ? 'checked':'' }}>
                <h5>{{ ucfirst($module['module_name']) }}</h5>
              </label>
              <ul type="none" class="sub-checkbox">
                <?php foreach ($module['sub_modules'] as $sub_modules) { ?>

                  <li class="spacing-top"><label><input type="checkbox" name="access_id[]" value="{{ $sub_modules['id'] }}" {{ (in_array($sub_modules['id'],$available_rights)) ? 'checked':'' }}>{{ ucfirst($sub_modules['submodule_name']) }}</label></li>

                <?php } ?>
              </ul>
            </div>
          </div>
        <?php } ?>
      </div>

    </div>
  <?php } ?>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.acc_heading_chkbox').click(function() {

      if ($(this).is(':checked')) {
        $(this).parent().siblings('ul').find('input').prop('checked', true);
      } else {
        $(this).parent().siblings('ul').find('input').prop('checked', false);
      }
    });
  });

  // $(document).ready(function() {
  //   $('.select_all').click(function() {
  //     if ($(this).is(':checked')) {
  //       $(this).closest('form').find('input').prop('checked', true);
  //     } else {
  //       $(this).closest('form').find('input').prop('checked', false);
  //     }
  //   });
  // });
  $(document).ready(function() {
  $('.select_all').click(function() {
    let form = $(this).closest('form');
    form.find('input[type="checkbox"]').prop('checked', $(this).is(':checked'));
  });
  let form = $('.select_all').closest('form');
  let checkboxes = form.find('input[name="access_id[]"]').not('.select_all');
  let allChecked = checkboxes.length && checkboxes.filter(':checked').length === checkboxes.length;

  if (allChecked) {
    $('.select_all').prop('checked', true);
  }
});
</script>