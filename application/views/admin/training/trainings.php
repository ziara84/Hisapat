<?php echo message_box('success'); ?>
<?php echo message_box('error');
$created = can_action('101', 'created');
$edited = can_action('101', 'edited');
$deleted = can_action('101', 'deleted');
?>
<div class="panel panel-custom" style="border: none;" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            <?= lang('training') . ' ' . lang('list') ?>
            <?php if (!empty($created)) { ?>
                <div class="pull-right hidden-print" style="padding-top: 0px;padding-bottom: 8px">
                    <a href="<?= base_url() ?>admin/training/new_training" class="btn btn-xs btn-info"
                       data-toggle="modal"
                       data-placement="top" data-target="#myModal_large">
                        <i class="fa fa-plus "></i> <?= ' ' . lang('new') . ' ' . lang('training') ?></a>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('name') ?></th>
                <th><?= lang('course_training') ?></th>
                <th><?= lang('vendor') ?></th>
                <th><?= lang('start_date') ?></th>
                <th><?= lang('finish_date') ?></th>
                <?php $show_custom_fields = custom_form_table(15, null);
                if (!empty($show_custom_fields)) {
                    foreach ($show_custom_fields as $c_label => $v_fields) {
                        if (!empty($c_label)) {
                            ?>
                            <th><?= $c_label ?> </th>
                        <?php }
                    }
                }
                ?>
                <th><?= lang('status') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($all_training_info)):foreach ($all_training_info as $key => $v_training):
                $profile_info = $this->db->where('user_id', $v_training->user_id)->get('tbl_account_details')->row();
                $can_edit = $this->training_model->can_action('tbl_training', 'edit', array('training_id' => $v_training->training_id));
                $can_delete = $this->training_model->can_action('tbl_training', 'delete', array('training_id' => $v_training->training_id));
                if (!empty($profile_info->fullname)) {
                    $name = $profile_info->fullname . ' (' . $profile_info->employment_id . ')';
                } else {
                    $name = '-';
                }
                ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td><?php echo $v_training->training_name; ?></td>
                    <td><?php echo $v_training->vendor_name; ?></td>
                    <td><?= strftime(config_item('date_format'), strtotime($v_training->start_date)) ?></td>
                    <td><?= strftime(config_item('date_format'), strtotime($v_training->finish_date)) ?></td>
                    <?php $show_custom_fields = custom_form_table(15, $v_training->training_id);
                    if (!empty($show_custom_fields)) {
                        foreach ($show_custom_fields as $c_label => $v_fields) {
                            if (!empty($c_label)) {
                                ?>
                                <td><?= $v_fields ?> </td>
                            <?php }
                        }
                    }
                    ?>
                    <td><?php
                        if ($v_training->status == '0') {
                            echo '<span class="label label-warning">' . lang('pending') . ' </span>';
                        } elseif ($v_training->status == '1') {
                            echo '<span class="label label-info">' . lang('started') . '</span>';
                        } elseif ($v_training->status == '2') {
                            echo '<span class="label label-success"> ' . lang('completed') . ' </span>';
                        } else {
                            echo '<span class="label label-danger"> ' . lang('terminated ') . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if (!empty($can_edit) && !empty($edited)) { ?>
                            <span data-toggle="tooltip" data-placement="top" title="<?= lang('edit') ?>">
                        <a href="<?= base_url() ?>admin/training/new_training/<?= $v_training->training_id ?>"
                           class="btn btn-primary btn-xs"
                           data-toggle="modal"
                           data-placement="top" data-target="#myModal_large">
                            <i class="fa fa-pencil-square-o"></i> </a>
                            </span>
                        <?php }
                        if (!empty($can_delete) && !empty($deleted)) { ?>
                            <?php echo btn_delete('admin/training/delete_training/' . $v_training->training_id) ?>
                        <?php } ?>
                        <?php echo btn_view_modal('admin/training/training_details/' . $v_training->training_id) ?>

                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>