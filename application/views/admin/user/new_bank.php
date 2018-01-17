<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('new_bank') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url() ?>admin/user/update_bank_info/<?php echo $profile_info->account_details_id; ?>/<?php
              if (!empty($bank_info->employee_bank_id)) {
                  echo $bank_info->employee_bank_id;
              }
              ?>"
              method="post" class="form-horizontal form-groups-bordered">

            <!-- CV Upload -->
            <div class="">
                <label class="control-label"><?= lang('bank') . ' ' . lang('name') ?> <span class="text-danger">*</span></label>
                <input required type="text" name="bank_name" value="<?php
                if (!empty($bank_info->bank_name)) {
                    echo $bank_info->bank_name;
                }
                ?>" class="form-control">
                <input type="hidden" name="employee_bank_id" value="<?php
                if (!empty($bank_info->employee_bank_id)) {
                    echo $bank_info->employee_bank_id;
                }
                ?>" class="form-control">
            </div>
            <div class="">
                <label class="control-label"><?= lang('branch') . ' ' . lang('name') ?><span
                        class="text-danger">*</span></label>
                <input type="text" required name="branch_name" value="<?php
                if (!empty($bank_info->branch_name)) {
                    echo $bank_info->branch_name;
                }
                ?>" class="form-control">
            </div>
            <div class="">
                <label class="control-label"><?= lang('account_name') ?><span class="text-danger">*</span></label>
                <input required type="text" name="account_name" value="<?php
                if (!empty($bank_info->account_name)) {
                    echo $bank_info->account_name;
                }
                ?>" class="form-control">
            </div>
            <div class="">
                <label
                    class="control-label"><?= lang('account_number') ?><span class="text-danger">*</span></label>
                <input required type="text" name="account_number" value="<?php
                if (!empty($bank_info->account_number)) {
                    echo $bank_info->account_number;
                }
                ?>" class="form-control">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
            </div>
        </form>
    </div>
</div>