<!-- start modal-->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close"
                        data-dismiss="modal">&times;
                </button>
                <h4 class="modal-title">Modal Header</h4>
            </div>
            <div class="modal-body">
                <form role="form" enctype="multipart/form-data" id="form" data-parsley-validate="" novalidate=""
                      action="<?php echo base_url(); ?>admin/client/save_client/<?php
                      if (!empty($client_info)) {
                          echo $client_info->client_id;
                      }
                      ?>" method="post" class="form-horizontal  ">

                    <div class="form-group">
                        <label class="col-lg-3 control-label">name
                            <span
                                    class="text-danger"> *</span></label>
                        <div class="col-lg-5">
                            <input type="text" class="form-control" required=""
                                   value="<?php
                                   if (!empty($client_info->name)) {
                                       echo $client_info->name;
                                   }
                                   ?>" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label
                                class="col-lg-3 control-label">mobile</label>
                        <div class="col-lg-5">
                            <input type="text" class="form-control"
                                   value="<?php
                                   if (!empty($client_info->mobile)) {
                                       echo $client_info->mobile;
                                   }
                                   ?>" name="mobile">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-success"><?= lang('save') ?></button>
                        <button type="button" class="btn btn-danger"  data-dismiss="modal">Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end modal-->
<form name="myform" role="form" data-parsley-validate="" novalidate=""
      enctype="multipart/form-data" id="form" action="<?php echo base_url(); ?>admin/invoice/save_invoice/<?php
if (!empty($invoice_info)) {
    echo $invoice_info->invoices_id;
}
?>" method="post" class="form-horizontal  ">
    <div
            class="<?php if (!isset($invoice_info) || (isset($invoice_info) && !empty($invoices_to_merge) && count($invoices_to_merge) == 0)) {
                echo ' hide';
            } ?>" id="invoice_top_info">
        <div class="panel-body">
            <div class="row">
                <div id="merge" class="col-md-8">
                    <?php if (isset($invoice_info) && !empty($invoices_to_merge)) {
                        $this->load->view('admin/invoice/merge_invoice', array('invoices_to_merge' => $invoices_to_merge));
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php include_once 'assets/js/sales.php'; ?>
    <?php echo message_box('success'); ?>
    <?php echo message_box('error'); ?>
    <?php
    $mdate = date('Y-m-d');
    $last_7_days = date('Y-m-d', strtotime('today - 7 days'));
    $all_goal_tracking = $this->invoice_model->get_permission('tbl_goal_tracking');

    $all_goal = 0;
    $bank_goal = 0;
    $complete_achivement = 0;
    if (!empty($all_goal_tracking)) {
        foreach ($all_goal_tracking as $v_goal_track) {
            $goal_achieve = $this->invoice_model->get_progress($v_goal_track, true);
            if ($v_goal_track->goal_type_id == 5) {
                if ($v_goal_track->end_date <= $mdate) { // check today is last date or not

                    if ($v_goal_track->email_send == 'no') {// check mail are send or not
                        if ($v_goal_track->achievement <= $goal_achieve['achievement']) {
                            if ($v_goal_track->notify_goal_achive == 'on') {// check is notify is checked or not check
                                $this->invoice_model->send_goal_mail('goal_achieve', $v_goal_track);
                            }
                        } else {
                            if ($v_goal_track->notify_goal_not_achive == 'on') {// check is notify is checked or not check
                                $this->invoice_model->send_goal_mail('goal_not_achieve', $v_goal_track);
                            }
                        }
                    }
                }
                $all_goal += $v_goal_track->achievement;
                $complete_achivement += $goal_achieve['achievement'];
            }
        }
    }
    // 30 days before

    for ($iDay = 7; $iDay >= 0; $iDay--) {
        $date = date('Y-m-d', strtotime('today - ' . $iDay . 'days'));
        $where = array('date_saved >=' => $date . " 00:00:00", 'date_saved <=' => $date . " 23:59:59");
        $invoice_result[$date] = count($this->db->where($where)->get('tbl_invoices')->result());
    }

    $terget_achievement = $this->db->where(array('goal_type_id' => 5, 'start_date >=' => $last_7_days, 'end_date <=' => $mdate))->get('tbl_goal_tracking')->result();

    $total_terget = 0;
    if (!empty($terget_achievement)) {
        foreach ($terget_achievement as $v_terget) {
            $total_terget += $v_terget->achievement;
        }
    }
    $tolal_goal = $all_goal + $bank_goal;
    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
    if ($this->session->userdata('user_type') == 1) {
        $margin = 'margin-bottom:30px';
        $h_s = config_item('invoice_state');
        ?>
        <div id="state_report" style="display: none">
            <div class="col-sm-12 bg-white p0" style="<?= $margin ?>">
                <div class="col-md-4">
                    <div class="row row-table pv-lg">
                        <div class="col-xs-6">
                            <p class="m0 lead"><?= ($tolal_goal) ?></p>
                            <p class="m0">
                                <small><?= lang('achievement') ?></small>
                            </p>
                        </div>
                        <div class="col-xs-6 ">
                            <p class="m0 lead"><?= ($total_terget) ?></p>
                            <p class="m0">
                                <small><?= lang('last_weeks') . ' ' . lang('created') ?></small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row row-table ">
                        <div class="col-xs-6">
                            <p class="m0 lead"><?= ($complete_achivement) ?></p>
                            <p class="m0">
                                <small><?= lang('completed') . ' ' . lang('achievements') ?></small>
                            </p>
                        </div>
                        <div class="col-xs-6 pt">
                            <div data-sparkline="" data-bar-color="#23b7e5" data-height="60" data-bar-width="8"
                                 data-bar-spacing="6" data-chart-range-min="0" values="<?php
                            if (!empty($invoice_result)) {
                                foreach ($invoice_result as $v_invoice_result) {
                                    echo $v_invoice_result . ',';
                                }
                            }
                            ?>">
                            </div>
                            <p class="m0">
                                <small>
                                    <?php
                                    if (!empty($invoice_result)) {
                                        foreach ($invoice_result as $date => $v_invoice_result) {
                                            echo date('d', strtotime($date)) . ' ';
                                        }
                                    }
                                    ?>
                                </small>
                            </p>

                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="row row-table ">
                        <div class="col-xs-6">
                            <p class="m0 lead">
                                <?php
                                if ($tolal_goal < $complete_achivement) {
                                    $pending_goal = 0;
                                } else {
                                    $pending_goal = $tolal_goal - $complete_achivement;
                                } ?>
                                <?= $pending_goal ?></p>
                            <p class="m0">
                                <small><?= lang('pending') . ' ' . lang('achievements') ?></small>
                            </p>
                        </div>
                        <?php
                        if (!empty($tolal_goal)) {
                            if ($tolal_goal <= $complete_achivement) {
                                $total_progress = 100;
                            } else {
                                $progress = ($complete_achivement / $tolal_goal) * 100;
                                $total_progress = round($progress);
                            }
                        } else {
                            $total_progress = 0;
                        }
                        ?>
                        <div class="col-xs-6 text-center pt">
                            <div class="inline ">
                                <div class="easypiechart text-success"
                                     data-percent="<?= $total_progress ?>"
                                     data-line-width="5" data-track-Color="#f0f0f0"
                                     data-bar-color="#<?php
                                     if ($total_progress == 100) {
                                         echo '8ec165';
                                     } elseif ($total_progress >= 40 && $total_progress <= 50) {
                                         echo '5d9cec';
                                     } elseif ($total_progress >= 51 && $total_progress <= 99) {
                                         echo '7266ba';
                                     } else {
                                         echo 'fb6b5b';
                                     }
                                     ?>" data-rotate="270" data-scale-Color="false"
                                     data-size="50"
                                     data-animate="2000">
                                                        <span class="small "><?= $total_progress ?>
                                                            %</span>
                                    <span class="easypie-text"><strong><?= lang('done') ?></strong></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php
            $client_outstanding = $this->invoice_model->all_outstanding();
            $currency = $this->db->where(array('code' => config_item('default_currency')))->get('tbl_currencies')->row();
            ?>
            <div class="row">
                <div class="col-lg-3">
                    <!-- START widget-->
                    <div class="panel widget">
                        <div class="panel-body pl-sm pr-sm pt-sm pb0 text-center">
                            <h3 class="mt0 mb0"><?php
                                if ($client_outstanding > 0) {
                                    echo display_money($client_outstanding, $currency->symbol);
                                } else {
                                    echo display_money(0, $currency->symbol);
                                }
                                ?></h3>
                            <p class="text-warning m0"><?= lang('total') . ' ' . lang('outstanding') . ' ' . lang('invoice') ?></p>
                        </div>
                    </div>
                </div>
                <!-- END widget-->
                <?php
                $past_overdue = 0;
                $all_paid_amount = 0;
                $not_paid = 0;
                $fully_paid = 0;
                $draft = 0;
                $partially_paid = 0;
                $overdue = 0;
                $all_invoices = $this->invoice_model->get_permission('tbl_invoices');

                if (!empty($all_invoices)) {
                    $all_invoices = array_reverse($all_invoices);
                    foreach ($all_invoices as $v_invoice) {
                        $payment_status = $this->invoice_model->get_payment_status($v_invoice->invoices_id);
                        if (strtotime($v_invoice->due_date) < time() AND $payment_status != lang('fully_paid')) {
                            $past_overdue += $this->invoice_model->calculate_to('invoice_due', $v_invoice->invoices_id);
                        }
                        $all_paid_amount += $this->invoice_model->calculate_to('paid_amount', $v_invoice->invoices_id);

                        if ($this->invoice_model->get_payment_status($v_invoice->invoices_id) == lang('not_paid')) {
                            $not_paid += count($v_invoice->invoices_id);
                        }
                        if ($this->invoice_model->get_payment_status($v_invoice->invoices_id) == lang('fully_paid')) {
                            $fully_paid += count($v_invoice->invoices_id);
                        }
                        if ($this->invoice_model->get_payment_status($v_invoice->invoices_id) == lang('draft')) {
                            $draft += count($v_invoice->invoices_id);
                        }
                        if ($this->invoice_model->get_payment_status($v_invoice->invoices_id) == lang('partially_paid')) {
                            $partially_paid += count($v_invoice->invoices_id);
                        }
                        if (strtotime($v_invoice->due_date) < time() AND $payment_status != lang('fully_paid')) {
                            $overdue += count($v_invoice->invoices_id);
                        }
                    }
                }

                ?>
                <div class="col-lg-3">
                    <!-- START widget-->
                    <div class="panel widget">
                        <div class="panel-body pl-sm pr-sm pt-sm pb0 text-center">
                            <h3 class="mt0 mb0 "><?= display_money($all_paid_amount + $client_outstanding, $currency->symbol) ?></h3>
                            <p class="text-primary m0"><?= lang('total') . ' ' . lang('invoice_amount') ?></p>
                        </div>
                    </div>
                    <!-- END widget-->
                </div>
                <div class="col-lg-3">
                    <!-- START widget-->
                    <div class="panel widget">
                        <div class="panel-body pl-sm pr-sm pt-sm pb0 text-center">
                            <h3 class="mt0 mb0"><?= display_money($past_overdue, $currency->symbol) ?></h3>
                            <p class="text-danger m0"><?= lang('past') . ' ' . lang('overdue') . ' ' . lang('invoice') ?></p>
                        </div>
                    </div>
                    <!-- END widget-->
                </div>
                <div class="col-lg-3">
                    <!-- START widget-->
                    <div class="panel widget">
                        <div class="panel-body pl-sm pr-sm pt-sm pb0 text-center">
                            <h3 class="mt0 mb0 "><?= display_money($all_paid_amount, $currency->symbol) ?></h3>
                            <p class="text-success m0"><?= lang('paid') . ' ' . lang('invoice') ?></p>
                        </div>
                    </div>
                    <!-- END widget-->
                </div>
            </div>
            <?php if (!empty($all_invoices)) { ?>
                <div class="row">
                    <div class="col-lg-5ths pl-lg">
                        <!-- START widget-->
                        <div class="panel widget">
                            <div class="pl-sm pr-sm pb-sm">
                                <strong><a style="font-size: 15px"
                                           href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/not_paid"><?= lang('unpaid') ?></a>
                                    <small class="pull-right " style="padding-top: 2px"> <?= $not_paid ?>
                                        / <?= count($all_invoices) ?></small>
                                </strong>
                                <div class="progress progress-striped progress-xs mb-sm">
                                    <div class="progress-bar progress-bar-danger " data-toggle="tooltip"
                                         data-original-title="<?= ($not_paid / count($all_invoices)) * 100 ?>%"
                                         style="width: <?= ($not_paid / count($all_invoices)) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END widget-->
                    </div>

                    <div class="col-lg-5ths">
                        <!-- START widget-->
                        <div class="panel widget">
                            <div class="pl-sm pr-sm pb-sm">
                                <strong><a style="font-size: 15px"
                                           href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/paid"><?= lang('paid') ?></a>
                                    <small class="pull-right " style="padding-top: 2px"> <?= $fully_paid ?>
                                        / <?= count($all_invoices) ?></small>
                                </strong>
                                <div class="progress progress-striped progress-xs mb-sm">
                                    <div class="progress-bar progress-bar-success " data-toggle="tooltip"
                                         data-original-title="<?= ($fully_paid / count($all_invoices)) * 100 ?>%"
                                         style="width: <?= ($fully_paid / count($all_invoices)) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END widget-->
                    </div>
                    <div class="col-lg-5ths">
                        <!-- START widget-->
                        <div class="panel widget">
                            <div class="pl-sm pr-sm pb-sm">
                                <strong><a style="font-size: 15px"
                                           href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/partially_paid"><?= lang('partially_paid') ?></a>
                                    <small class="pull-right " style="padding-top: 2px"> <?= $partially_paid ?>
                                        / <?= count($all_invoices) ?></small>
                                </strong>
                                <div class="progress progress-striped progress-xs mb-sm">
                                    <div class="progress-bar progress-bar-primary " data-toggle="tooltip"
                                         data-original-title="<?= ($partially_paid / count($all_invoices)) * 100 ?>%"
                                         style="width: <?= ($partially_paid / count($all_invoices)) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END widget-->
                    </div>
                    <div class="col-lg-5ths">
                        <!-- START widget-->
                        <div class="panel widget">
                            <div class="pl-sm pr-sm pb-sm">
                                <strong><a style="font-size: 15px"
                                           href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/overdue"><?= lang('overdue') ?></a>
                                    <small class="pull-right " style="padding-top: 2px"> <?= $overdue ?>
                                        / <?= count($all_invoices) ?></small>
                                </strong>
                                <div class="progress progress-striped progress-xs mb-sm">
                                    <div class="progress-bar progress-bar-warning " data-toggle="tooltip"
                                         data-original-title="<?= ($overdue / count($all_invoices)) * 100 ?>%"
                                         style="width: <?= ($overdue / count($all_invoices)) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END widget-->
                    </div>
                    <div class="col-lg-5ths pr-lg">
                        <!-- START widget-->
                        <div class="panel widget">
                            <div class="pl-sm pr-sm pb-sm">
                                <strong><a style="font-size: 15px"
                                           href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/draft"><?= lang('draft') ?></a>
                                    <small class="pull-right " style="padding-top: 2px"> <?= $draft ?>
                                        / <?= count($all_invoices) ?></small>
                                </strong>
                                <div class="progress progress-striped progress-xs mb-sm">
                                    <div class="progress-bar progress-bar-aqua " data-toggle="tooltip"
                                         data-original-title="<?= ($draft / count($all_invoices)) * 100 ?>%"
                                         style="width: <?= ($draft / count($all_invoices)) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END widget-->
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php }
    $type = $this->uri->segment(5);
    if (!empty($type) && !is_numeric($type)) {
        $ex = explode('_', $type);
        if ($ex[0] == 'c') {
            $c_id = $ex[1];
            $type = '_' . date('Y');
        }
    }
    if (empty($type)) {
        $type = '_' . date('Y');
    }
    ?>
    <div class="btn-group mb-lg pull-left mr">
        <button class=" btn btn-xs btn-white dropdown-toggle"
                data-toggle="dropdown">
            <i class="fa fa-search"></i>

            <?php
            echo lang('filter_by');
            if (!empty($type) && !is_numeric($type)) {
                $ex = explode('_', $type);
                if (!empty($ex)) {
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        echo ' : ' . $ex[1];
                    } else {
                        echo ' : ' . lang($type);
                    }
                } else {
                    echo ' : ' . lang($type);
                }

            } ?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu animated zoomIn">
            <li><a href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/all"><?= lang('all'); ?></a></li>
            <?php
            $invoiceFilter = $this->invoice_model->get_invoice_filter();
            if (!empty($invoiceFilter)) {
                foreach ($invoiceFilter as $v_Filter) {
                    ?>
                    <li <?php if ($v_Filter['value'] == $type) {
                        echo 'class="active"';
                    } ?> >
                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/filter_by/<?= $v_Filter['value'] ?>"><?= $v_Filter['name'] ?></a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </div>
    <?php
    if ($this->session->userdata('user_type') == 1) {
        if ($h_s == 'block') {
            $title = lang('hide_quick_state');
            $url = 'hide';
            $icon = 'fa fa-eye-slash';
        } else {
            $title = lang('view_quick_state');
            $url = 'show';
            $icon = 'fa fa-eye';
        }
        ?>
        <div onclick="slideToggle('#state_report')" id="quick_state" data-toggle="tooltip" data-placement="top"
             title="<?= $title ?>"
             class="btn-xs btn btn-purple pull-left">
            <i class="fa fa-bar-chart"></i>
        </div>
        <div class="btn-xs btn btn-white pull-left ml ">
            <a class="text-dark" id="change_report"
               href="<?= base_url() ?>admin/dashboard/change_report/<?= $url ?>"><i
                        class="<?= $icon ?>"></i>
                <span><?= ' ' . lang('quick_state') . ' ' . lang($url) . ' ' . lang('always') ?></span></a>
        </div>
        <?php
    }
    $created = can_action('13', 'created');
    $edited = can_action('13', 'edited');
    $deleted = can_action('13', 'deleted');
    if (!empty($created) || !empty($edited)){
    ?>
    <a data-toggle="modal" data-target="#myModal"
       href="<?= base_url() ?>admin/invoice/zipped/invoice"
       class="btn btn-success btn-xs ml-lg"><?= lang('zip_invoice') ?></a>

    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs">
                    <li class=""><a href="#manage"
                                                                        data-toggle="tab"><?= lang('all_invoices') ?></a>
                    </li>
                    <li class=" active"><a href="#create"
                                                                        data-toggle="tab"><?= lang('create_invoice') ?></a>
                    </li>
                </ul>
                <?php ?>
                <div class="tab-content bg-white">
                    <!-- ************** general *************-->
                    <div class="tab-pane " id="manage">
                        <?php } else { ?>
                        <div class="panel panel-custom">
                            <header class="panel-heading ">
                                <div class="panel-title"><strong><?= lang('all_invoices') ?></strong></div>
                            </header>
                            <?php } ?>
                            <div class="table-responsive">
                                <table class="table table-striped DataTables " id="DataTables" cellspacing="0"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th><?= lang('invoice') ?></th>
                                        <th class="col-date"><?= lang('invoice_date') ?></th>
                                        <th class="col-date"><?= lang('due_date') ?></th>
                                        <th><?= lang('client_name') ?></th>
                                        <th class="col-currency"><?= lang('due_amount') ?></th>
                                        <th><?= lang('status') ?></th>
                                        <?php $show_custom_fields = custom_form_table(9, null);
                                        if (!empty($show_custom_fields)) {
                                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                                if (!empty($c_label)) {
                                                    ?>
                                                    <th><?= $c_label ?> </th>
                                                <?php }
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($edited) || !empty($deleted)) { ?>
                                            <th class="hidden-print"><?= lang('action') ?></th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($all_invoices_info)) {
                                        $all_invoices_info = array_reverse($all_invoices_info);
                                        foreach ($all_invoices_info as $v_invoices) {
                                            $can_edit = $this->invoice_model->can_action('tbl_invoices', 'edit', array('invoices_id' => $v_invoices->invoices_id));
                                            $can_delete = $this->invoice_model->can_action('tbl_invoices', 'delete', array('invoices_id' => $v_invoices->invoices_id));

                                            if ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('fully_paid')) {
                                                $invoice_status = lang('fully_paid');
                                                $label = "success";
                                            } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('draft')) {
                                                $invoice_status = lang('draft');
                                                $label = "default";
                                            } elseif ($this->invoice_model->get_payment_status($v_invoices->invoices_id) == lang('partially_paid')) {
                                                $invoice_status = lang('partially_paid');
                                                $label = "warning";
                                            } elseif ($v_invoices->emailed == 'Yes') {
                                                $invoice_status = lang('sent');
                                                $label = "info";
                                            } else {
                                                $invoice_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                                                $label = "danger";
                                            }
                                            ?>
                                            <tr id="invoice_table_<?= $v_invoices->invoices_id ?>">
                                                <td><a class="text-info"
                                                       href="<?= base_url() ?>admin/invoice/manage_invoice/invoice_details/<?= $v_invoices->invoices_id ?>"><?= $v_invoices->reference_no ?></a>
                                                </td>
                                                <td><?= strftime(config_item('date_format'), strtotime($v_invoices->invoice_date)) ?></td>
                                                <td><?= strftime(config_item('date_format'), strtotime($v_invoices->due_date)) ?>
                                                    <?php
                                                    $payment_status = $this->invoice_model->get_payment_status($v_invoices->invoices_id);
                                                    if (strtotime($v_invoices->due_date) < time() AND $payment_status != lang('fully_paid')) { ?>
                                                        <span
                                                                class="label label-danger "><?= lang('overdue') ?></span>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                                <?php
                                                $client_info = $this->invoice_model->check_by(array('client_id' => $v_invoices->client_id), 'tbl_client');
                                                if (!empty($client_info)) {
                                                    $client_name = $client_info->name;
                                                } else {
                                                    $client_name = '-';
                                                }
                                                ?>
                                                <td><?= $client_name; ?></td>
                                                <?php $currency = $this->invoice_model->client_currency_sambol($v_invoices->client_id);
                                                if (empty($currency)) {
                                                    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
                                                }
                                                ?>
                                                <td><?= display_money($this->invoice_model->calculate_to('invoice_due', $v_invoices->invoices_id), $currency->symbol) ?></td>
                                                <td><span
                                                            class="label label-<?= $label ?>"><?= $invoice_status ?></span>
                                                    <?php if ($v_invoices->recurring == 'Yes') { ?>
                                                        <span data-toggle="tooltip" data-placement="top"
                                                              title="<?= lang('recurring') ?>"
                                                              class="label label-primary"><i
                                                                    class="fa fa-retweet"></i></span>
                                                    <?php } ?>

                                                </td>
                                                <?php $show_custom_fields = custom_form_table(9, $v_invoices->invoices_id);
                                                if (!empty($show_custom_fields)) {
                                                    foreach ($show_custom_fields as $c_label => $v_fields) {
                                                        if (!empty($c_label)) {
                                                            ?>
                                                            <td><?= $v_fields ?> </td>
                                                        <?php }
                                                    }
                                                }
                                                ?>
                                                <?php if (!empty($edited) || !empty($deleted)) { ?>
                                                    <td class="hidden-print">
                                                        <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                                            <?= btn_edit('admin/invoice/manage_invoice/create_invoice/' . $v_invoices->invoices_id) ?>
                                                        <?php }
                                                        if (!empty($can_delete) && !empty($deleted)) {
                                                            ?>
                                                            <?php echo ajax_anchor(base_url("admin/invoice/delete/delete_invoice/" . $v_invoices->invoices_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#invoice_table_" . $v_invoices->invoices_id)); ?>
                                                        <?php } ?>
                                                        <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                                            <div class="btn-group">
                                                                <button class="btn btn-xs btn-default dropdown-toggle"
                                                                        data-toggle="dropdown">
                                                                    <?= lang('change_status') ?>
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu animated zoomIn">
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/invoice_details/<?= $v_invoices->invoices_id ?>"><?= lang('preview_invoice') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/payment/<?= $v_invoices->invoices_id ?>"><?= lang('pay_invoice') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/email_invoice/<?= $v_invoices->invoices_id ?>"><?= lang('email_invoice') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/send_reminder/<?= $v_invoices->invoices_id ?>"><?= lang('send_reminder') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/send_overdue/<?= $v_invoices->invoices_id ?>"><?= lang('send_invoice_overdue') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/manage_invoice/invoice_history/<?= $v_invoices->invoices_id ?>"><?= lang('invoice_history') ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="<?= base_url() ?>admin/invoice/pdf_invoice/<?= $v_invoices->invoices_id ?>"><?= lang('pdf') ?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (!empty($created) || !empty($edited)) { ?>
                        <div class="tab-pane active" style="padding: 10px" id="create">
                            <?php
                            if (!empty($invoice_info)) {
                                $client_info = $this->invoice_model->check_by(array('client_id' => $invoice_info->client_id), 'tbl_client');
                                if (!empty($client_info)) {
                                    $client_lang = $client_info->language;
                                    $currency = $this->invoice_model->client_currency_sambol($invoice_info->client_id);
                                } else {
                                    $client_lang = 'english';
                                    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
                                }
                            } else {
                                $client_lang = 'english';
                                $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
                            }
                            unset($this->lang->is_loaded[5]);
                            $language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
                            ?>

                            <div class="row mb-lg invoice accounting-template">
                                <div class="col-md-6 br pv" style="padding: 10px">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                                        class="text-danger">*</span></label>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" value="<?php
                                                if (!empty($invoice_info)) {
                                                    echo $invoice_info->reference_no;
                                                } else {
                                                    echo config_item('invoice_prefix');
                                                    if (config_item('increment_invoice_number') == 'FALSE') {
                                                        $this->load->helper('string');
                                                        echo random_string('nozero', 6);
                                                    } else {
                                                        echo $this->invoice_model->generate_invoice_number();
                                                    }
                                                }
                                                ?>" name="reference_no">
                                            </div>
                                            <div class="col-lg-3">
                                            <div class="btn btn-xs btn-info"
                                                 id="start_recurring"><?= lang('recurring') ?></div>

                                        </div>
                                        </div>
                                        <div id="recurring" class="hide">
                                            <div class="form-group">
                                                <label
                                                        class="col-lg-3 control-label"><?= lang('recur_frequency') ?> </label>
                                                <div class="col-lg-7">
                                                    <select name="recuring_frequency" id="recuring_frequency"
                                                            class="form-control">
                                                        <option value="none"><?= lang('none') ?></option>
                                                        <option
                                                                value="7D"><?= lang('week') ?></option>
                                                        <option
                                                                value="1M"><?= lang('month') ?></option>
                                                        <option
                                                                value="3M"><?= lang('quarter') ?></option>
                                                        <option
                                                                value="6M"><?= lang('six_months') ?></option>
                                                        <option
                                                                value="1Y"><?= lang('1year') ?></option>
                                                        <option
                                                                value="2Y"><?= lang('2year') ?></option>
                                                        <option
                                                                value="3Y"><?= lang('3year') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                          
                                            <div class="form-group">
                                                <label
                                                        class="col-lg-3 control-label"><?= lang('start_date') ?></label>
                                                <div class="col-lg-7">
                                                    <?php
                                                    if (!empty($invoice_info) && $invoice_info->recurring == 'Yes') {
                                                        $recur_start_date = date('Y-m-d', strtotime($invoice_info->recur_start_date));
                                                        $recur_end_date = date('Y-m-d', strtotime($invoice_info->recur_end_date));
                                                    } else {
                                                        $recur_start_date = date('Y-m-d');
                                                        $recur_end_date = date('Y-m-d');
                                                    }
                                                    ?>
                                                    <div class="input-group">
                                                        <input class="form-control datepicker" type="text"
                                                               value="<?= $recur_start_date; ?>"
                                                               name="recur_start_date"
                                                               data-date-format="<?= config_item('date_picker_format'); ?>">
                                                        <div class="input-group-addon">
                                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label
                                                        class="col-lg-3 control-label"><?= lang('end_date') ?></label>
                                                <div class="col-lg-7">
                                                    <div class="input-group">
                                                        <input class="form-control datepicker" type="text"
                                                               value="<?= $recur_end_date; ?>"
                                                               name="recur_end_date"
                                                               data-date-format="<?= config_item('date_picker_format'); ?>">
                                                        <div class="input-group-addon">
                                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        </div>



                                        <div class="f_client_id">
                                            <div class="form-group">


                                                <label class="col-lg-3 control-label"><?= lang('client') ?>
                                                </label>
                                                <div class="col-lg-5">
                                                    <select class="form-control select_box" style="width: 100%"
                                                            name="client_id" value="New Client"
                                                            onchange="get_project_by_id(this.value)">
                                                        <option value="new client " selected="selected">new client
                                                        </option>
                                                        <!--                                                        <option value="">-->
                                                        <? //= lang('select') . ' ' . lang('client') ?><!--</option>-->
                                                        <?php
                                                        if (!empty($all_client)) {
                                                            foreach ($all_client as $v_client) {
                                                                if (!empty($project_info->client_id)) {
                                                                    $client_id = $project_info->client_id;
                                                                } elseif (!empty($invoice_info->client_id)) {
                                                                    $client_id = $invoice_info->client_id;
                                                                } elseif (!empty($c_id)) {
                                                                    $client_id = $c_id;
                                                                }
                                                                ?>
                                                                <option value="<?= $v_client->client_id ?>"
                                                                    <?php
                                                                    if (!empty($client_id)) {
                                                                        echo $client_id == $v_client->client_id ? 'selected' : null;
                                                                    }
                                                                    ?>
                                                                ><?= ucfirst($v_client->name) ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- button open modal-->
                                                <div class="col-lg-2">
                                               
                                                    <button type="button" data-toggle="modal" data-target="#myModal"
                                                            class="btn btn-success">Add Client
                                                    </button>
                                               
                                                </div>
                                            </div>

                                        </div>




                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                            <div class="col-lg-7">
                                                <select class="form-control" style="width: 100%"
                                                        name="project_id"
                                                        id="client_project">
                                                    <option value=""><?= lang('none') ?></option>
                                                    <?php

                                                    if (!empty($client_id)) {

                                                        if (!empty($project_info->project_id)) {
                                                            $project_id = $project_info->project_id;
                                                        } elseif ($invoice_info->project_id) {
                                                            $project_id = $invoice_info->project_id;
                                                        }
                                                        $all_project = $this->db->where('client_id', $client_id)->get('tbl_project')->result();
                                                        if (!empty($all_project)) {
                                                            foreach ($all_project as $v_project) {
                                                                ?>
                                                                <option value="<?= $v_project->project_id ?>" <?php
                                                                if (!empty($project_id)) {
                                                                    echo $v_project->project_id == $project_id ? 'selected' : '';
                                                                }
                                                                ?>><?= $v_project->project_name ?></option>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label
                                                    class="col-lg-3 control-label"><?= lang('invoice_date') ?></label>
                                            <div class="col-lg-7">
                                                <div class="input-group">
                                                    <input type="text" name="invoice_date"
                                                           class="form-control datepicker"
                                                           value="<?php
                                                           if (!empty($invoice_info->invoice_date)) {
                                                               echo $invoice_info->invoice_date;
                                                           } else {
                                                               echo date('Y-m-d');
                                                           }
                                                           ?>"
                                                           data-date-format="<?= config_item('date_picker_format'); ?>">
                                                    <div class="input-group-addon">
                                                        <a href="#"><i class="fa fa-calendar"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('due_date') ?></label>
                                            <div class="col-lg-7">
                                                <div class="input-group">
                                                    <input type="text" name="due_date"
                                                           class="form-control datepicker"
                                                           value="<?php
                                                           if (!empty($invoice_info->due_date)) {
                                                               echo $invoice_info->due_date;
                                                           } else {
                                                               echo strftime(date('Y-m-d', strtotime("+" . config_item('invoices_due_after') . " days")));
                                                           }
                                                           ?>"
                                                           data-date-format="<?= config_item('date_picker_format'); ?>">
                                                    <div class="input-group-addon">
                                                        <a href="#"><i class="fa fa-calendar"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" style="display:none;">
                                            <label for="discount_type"
                                                   class="control-label col-sm-3"><?= lang('discount_type') ?></label>
                                            <div class="col-sm-7">
                                                <select name="discount_type" class="selectpicker" data-width="100%">
                                                    <option value=""
                                                            selected><?php echo lang('no') . ' ' . lang('discount'); ?></option>
                                                    <option value="before_tax" <?php
                                                    if (isset($invoice_info)) {
                                                        if ($invoice_info->discount_type == 'before_tax') {
                                                            //echo 'selected';
                                                        }
                                                    } 
													echo 'selected';
													?>><?php echo lang('before_tax'); ?></option>
                                                    <option value="after_tax" <?php if (isset($invoice_info)) {
                                                        if ($invoice_info->discount_type == 'after_tax') {
                                                           // echo 'selected';
                                                        }
                                                    } ?>><?php echo lang('after_tax'); ?></option>
                                                </select>
                                                   <input type="hidden" name="discount_type" value="before_tax" />
                                            </div>
                                        </div>

                                        <div class="form-group" id="border-none"  style="display:none;">
                                            <label for="field-1"
                                                   class="col-sm-3 control-label"><?= lang('permission') ?> <span
                                                        class="required">*</span></label>
                                            <div class="col-sm-9">
                                                <div class="checkbox c-radio needsclick">
                                                    <label class="needsclick">
                                                        <input id="" <?php
                                                        if (!empty($invoice_info->permission) && $invoice_info->permission == 'all') {
                                                            echo 'checked';
                                                        } elseif (empty($invoice_info)) {
                                                            echo 'checked';
                                                        }
                                                        ?> type="radio" name="permission" value="everyone">
                                                        <span class="fa fa-circle"></span><?= lang('everyone') ?>
                                                        <i title="<?= lang('permission_for_all') ?>"
                                                           class="fa fa-question-circle" data-toggle="tooltip"
                                                           data-placement="top"></i>
                                                    </label>
                                                </div>
                                                 <input type="hidden" name="permission" value="everyone" />
                                                <div class="checkbox c-radio needsclick">
                                                    <label class="needsclick">
                                                        <input id="" <?php
                                                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                            echo 'checked';
                                                        }
                                                        ?> type="radio" name="permission" value="custom_permission"
                                                        >
                                                        <span
                                                                class="fa fa-circle"></span><?= lang('custom_permission') ?>
                                                        <i
                                                                title="<?= lang('permission_for_customization') ?>"
                                                                class="fa fa-question-circle" data-toggle="tooltip"
                                                                data-placement="top"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group <?php
                                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                            echo 'show';
                                        }
                                        ?>" id="permission_user_1">
                                            <label for="field-1"
                                                   class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('users') ?>
                                                <span
                                                        class="required">*</span></label>
                                            <div class="col-sm-9">
                                                <?php
                                                if (!empty($permission_user)) {
                                                    foreach ($permission_user as $key => $v_user) {

                                                        if ($v_user->role_id == 1) {
                                                            $role = '<strong class="badge btn-danger">' . lang('admin') . '</strong>';
                                                        } else {
                                                            $role = '<strong class="badge btn-primary">' . lang('staff') . '</strong>';
                                                        }

                                                        ?>
                                                        <div class="checkbox c-checkbox needsclick">
                                                            <label class="needsclick">
                                                                <input type="checkbox"
                                                                    <?php
                                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                                        $get_permission = json_decode($invoice_info->permission);
                                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                                            if ($user_id == $v_user->user_id) {
                                                                                echo 'checked';
                                                                            }
                                                                        }

                                                                    }
                                                                    ?>
                                                                       value="<?= $v_user->user_id ?>"
                                                                       name="assigned_to[]"
                                                                       class="needsclick">
                                                                <span
                                                                        class="fa fa-check"></span><?= $v_user->username . ' ' . $role ?>
                                                            </label>

                                                        </div>
                                                        <div class="action_1 p
                                                <?php

                                                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                            $get_permission = json_decode($invoice_info->permission);

                                                            foreach ($get_permission as $user_id => $v_permission) {
                                                                if ($user_id == $v_user->user_id) {
                                                                    echo 'show';
                                                                }
                                                            }

                                                        }
                                                        ?>
                                                " id="action_1<?= $v_user->user_id ?>">
                                                            <label class="checkbox-inline c-checkbox">
                                                                <input id="<?= $v_user->user_id ?>" checked
                                                                       type="checkbox"
                                                                       name="action_1<?= $v_user->user_id ?>[]"
                                                                       disabled
                                                                       value="view">
                                                                <span
                                                                        class="fa fa-check"></span><?= lang('can') . ' ' . lang('view') ?>
                                                            </label>
                                                            <label class="checkbox-inline c-checkbox">
                                                                <input id="<?= $v_user->user_id ?>"
                                                                    <?php

                                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                                        $get_permission = json_decode($invoice_info->permission);

                                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                                            if ($user_id == $v_user->user_id) {
                                                                                if (in_array('edit', $v_permission)) {
                                                                                    echo 'checked';
                                                                                };

                                                                            }
                                                                        }

                                                                    }
                                                                    ?>
                                                                       type="checkbox"
                                                                       value="edit"
                                                                       name="action_<?= $v_user->user_id ?>[]">
                                                                <span
                                                                        class="fa fa-check"></span><?= lang('can') . ' ' . lang('edit') ?>
                                                            </label>
                                                            <label class="checkbox-inline c-checkbox">
                                                                <input id="<?= $v_user->user_id ?>"
                                                                    <?php

                                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                                        $get_permission = json_decode($invoice_info->permission);
                                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                                            if ($user_id == $v_user->user_id) {
                                                                                if (in_array('delete', $v_permission)) {
                                                                                    echo 'checked';
                                                                                };
                                                                            }
                                                                        }

                                                                    }
                                                                    ?>
                                                                       name="action_<?= $v_user->user_id ?>[]"
                                                                       type="checkbox"
                                                                       value="delete">
                                                                <span
                                                                        class="fa fa-check"></span><?= lang('can') . ' ' . lang('delete') ?>
                                                            </label>
                                                            <input id="<?= $v_user->user_id ?>" type="hidden"
                                                                   name="action_<?= $v_user->user_id ?>[]"
                                                                   value="view">

                                                        </div>


                                                        <?php
                                                    }
                                                }
                                                ?>


                                            </div>
                                        </div>
                                        <?php
                                        if (!empty($invoice_info)) {
                                            $invoices_id = $invoice_info->invoices_id;
                                        } else {
                                            $invoices_id = null;
                                        }
                                        ?>
                                        <?= custom_form_Fields(9, $invoices_id); ?>

                                    </div>
                                </div>
                                <div class="col-md-6 br pv" style="padding: 10px">

                                    <div class="row">
                                        <div class="form-group">
                                            <label for="field-1"
                                                   class="col-sm-4 control-label"><?= lang('sales') . ' ' . lang('agent') ?></label>
                                            <div class="col-sm-7">
                                                <select class="form-control select_box" required style="width: 100%"
                                                        name="user_id">
                                                    <option
                                                            value=""><?= lang('select') . ' ' . lang('sales') . ' ' . lang('agent') ?></option>
                                                    <?php
                                                    $all_user = $this->db->where('role_id != ', 2)->get('tbl_users')->result();
                                                    if (!empty($all_user)) {
                                                        foreach ($all_user as $v_user) {
                                                            $profile_info = $this->db->where('user_id', $v_user->user_id)->get('tbl_account_details')->row();
                                                            if (!empty($profile_info)) {
                                                                ?>
                                                                <option value="<?= $v_user->user_id ?>"
                                                                    <?php
                                                                    if (!empty($invoice_info->user_id)) {
                                                                        echo $invoice_info->user_id == $v_user->user_id ? 'selected' : null;
                                                                    } else {
                                                                        echo $this->session->userdata('user_id') == $v_user->user_id ? 'selected' : null;
                                                                    }
                                                                    ?>
                                                                ><?= $profile_info->fullname ?></option>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                         <input type="hidden" name="allow_paypal" value="Yes" />
                                         <input type="hidden" name="allow_stripe" value="no" />
                                         <input type="hidden" name="allow_2checkout" value="no" />
                                         <input type="hidden" name="allow_authorize" value="no" />
                                         <input type="hidden" name="allow_ccavenue" value="no" />
                                         <input type="hidden" name="allow_braintree" value="no" />
                                         <input type="hidden" name="allow_mollie" value="no" />
                                         <input type="hidden" name="allow_payumoney" value="no" />
                                        <?php
										$show_pay=0;
										if($show_pay==1){ if (config_item('paypal_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_paypal') ?></label>
                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_paypal == 'Yes') {
                                                                    echo 'checked';
                                                                } ?> name="allow_paypal">
                                                            <span class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                        <?php if (config_item('stripe_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_stripe') ?></label>
                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_stripe == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_stripe"><span class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('2checkout_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_2checkout') ?></label>

                                                <div class="col-sm-7">

                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_2checkout == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_2checkout"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('authorize_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_authorize.net') ?></label>

                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_authorize == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_authorize"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('ccavenue_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_ccavenue') ?></label>

                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_ccavenue == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_ccavenue"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('braintree_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_braintree') ?></label>

                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_braintree == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_braintree"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('mollie_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_mollie') ?></label>

                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_mollie == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_mollie"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (config_item('payumoney_status') == 'active'): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('allow_payumoney') ?></label>

                                                <div class="col-sm-7">
                                                    <div class="checkbox c-checkbox">
                                                        <label class="needsclick">
                                                            <input type="checkbox" value="Yes"
                                                                <?php if (!empty($invoice_info) && $invoice_info->allow_payumoney == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>
                                                                   name="allow_payumoney"><span
                                                                    class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; 
										}
										?>

                                        <?php if (!empty($project_id)): ?>
                                            <div class="form-group">
                                                <label for="field-1"
                                                       class="col-sm-4 control-label"><?= lang('visible_to_client') ?>
                                                    <span class="required">*</span></label>
                                                <div class="col-sm-8">
                                                    <input data-toggle="toggle" name="client_visible" value="Yes" <?php
                                                    if (!empty($invoice_info->client_visible) && $invoice_info->client_visible == 'Yes') {
                                                        echo 'checked';
                                                    }
                                                    ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>"
                                                           data-onstyle="success" data-offstyle="danger"
                                                           type="checkbox">
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                                <div class="col-sm-12" style="display:none;">
                                    <div class="">
                                        <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                                        <div class="col-lg-11 row">
                        <textarea name="notes" class="textarea_"><?php
                            if (!empty($invoice_info)) {
                                echo $invoice_info->notes;
                            } else {
                                echo $this->config->item('default_terms');
                            }
                            ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <style type="text/css">
                                .dropdown-menu > li > a {
                                    white-space: normal;
                                }

                                .dragger {
                                    background: url(../../assets/img/dragger.png) 10px 32px no-repeat;
                                    cursor: pointer;
                                }

                                <?php if (!empty($invoice_info)) { ?>
                                .dragger {
                                    background: url(../../../../assets/img/dragger.png) 10px 32px no-repeat;
                                    cursor: pointer;
                                }

                                <?php }?>
                                .input-transparent {
                                    box-shadow: none;
                                    outline: 0;
                                    border: 0 !important;
                                    background: 0 0;
                                    padding: 3px;
                                }
								
							label.formobile{display:none;}
							@media only screen and (max-width: 580px) {
							 label.formobile{display:inline-block; width:100%; text-align:left;}
							 thead{display:none !important;}
							 div..modal-dialog{margin-top:800px !important;}
							 div#start_recurring{margin-top:10px;}
							 div.f_client_id button{margin-top:10px;}
							}

                            </style>
                            <?php
                            $saved_items = $this->invoice_model->get_all_items();

                            ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="item_select" class="selectpicker m0" data-width="100%"
                                                id="item_select"
                                                data-none-selected-text="<?php echo lang('add_items'); ?>"
                                                data-live-search="true">
                                            <option value=""></option>
                                            <?php
                                            if (!empty($saved_items)) {
                                                $saved_items = array_reverse($saved_items, true);
                                                foreach ($saved_items as $group_id => $v_saved_items) {
                                                    if ($group_id != 0) {
                                                        $group = $this->db->where('customer_group_id', $group_id)->get('tbl_customer_group')->row()->customer_group;
                                                    } else {
                                                        $group = '';
                                                    }
                                                    ?>
                                                    <optgroup data-group-id="<?php echo $group_id; ?>"
                                                              label="<?php echo $group; ?>">
                                                        <?php
                                                        if (!empty($v_saved_items)) {
                                                            foreach ($v_saved_items as $v_item) { ?>
                                                                <option value="<?php echo $v_item->saved_items_id; ?>"
                                                                        data-subtext="<?php echo strip_tags(mb_substr($v_item->item_desc, 0, 200)) . '...'; ?>">
                                                                    (<?= display_money($v_item->unit_cost, $currency->symbol); ?>
                                                                    ) <?php echo $v_item->item_name; ?></option>
                                                            <?php }
                                                        }
                                                        ?>
                                                    </optgroup>

                                                <?php } ?>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 pull-right" style="display:none;">
                                    <div class="form-group">
                                        <label
                                                class="col-sm-4 control-label"><?php echo lang('show_quantity_as'); ?></label>
                                        <div class="col-sm-8">
                                            <label class="radio-inline c-radio">
                                                <input type="radio" value="qty" id="<?php echo lang('qty'); ?>"
                                                       name="show_quantity_as"
                                                    <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty') {
                                                        echo 'checked';
                                                    } else if (!isset($hours_quantity) && !isset($qty_hrs_quantity)) {
                                                        echo 'checked';
                                                    } ?>>
                                                <span class="fa fa-circle"></span><?php echo lang('qty'); ?>
                                            </label>
                                            <label class="radio-inline c-radio">
                                                <input type="radio" value="hours" id="<?php echo lang('hours'); ?>"
                                                       name="show_quantity_as" <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                                                    echo 'checked';
                                                } ?>>
                                                <span class="fa fa-circle"></span><?php echo lang('hours'); ?></label>
                                            <label class="radio-inline c-radio">
                                                <input type="radio" value="qty_hours"
                                                       id="<?php echo lang('qty') . '/' . lang('hours'); ?>"
                                                       name="show_quantity_as"
                                                    <?php if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty_hours' || isset($qty_hrs_quantity)) {
                                                        echo 'checked';
                                                    } ?>>
                                                <span
                                                        class="fa fa-circle"></span><?php echo lang('qty') . '/' . lang('hours'); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                               <input type="hidden" name="show_quantity_as" value="qty_hours" />
                                <div class="table-responsive s_table">
                                    <table class="table invoice-items-table items" style="width:100% !important">
                                        <thead style="background: #e8e8e8">
                                        <tr>                                           
                                            <th>
                                            <div class="row">
                                             <div class="col-lg-2 col-sm-8">
											  <?= $language_info['item_name'] ?>
                                             </div>
                                            <div class="col-lg-2 col-sm-8">
											  <?= $language_info['description'] ?>
                                              </div>
                                             
                                            <?php
                                            $invoice_view = config_item('invoice_view');
                                            if (!empty($invoice_view) && $invoice_view == '2') {
                                                ?>
                                               <div class="col-lg-2 col-sm-8">
												<?= $language_info['hsn_code'] ?>
                                                </div>
                                            <?php } ?>
                                            
                                               
                                            <?php
                                            $qty_heading = $language_info['qty'];
                                            if (isset($invoice_info) && $invoice_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                                                $qty_heading = lang('hours');
                                            } else if (isset($invoice_info) && $invoice_info->show_quantity_as == 'qty_hours') {
                                                $qty_heading = lang('qty') . '/' . lang('hours');
                                            }
											$qty_heading = lang('qty') . '/' . lang('hours');
                                            ?>
                                            <div class="col-lg-1 col-sm-8"><?php echo $qty_heading; ?></div>
                                            <div class="col-lg-2 col-sm-8"><?= $language_info['price'] ?></div>
                                            <div class="col-lg-2 col-sm-8"><?= $language_info['tax_rate'] ?></div>
                                           <div class="col-lg-2 col-sm-8"><?= $language_info['total'] ?></div>
                                           <div class="col-lg-1 col-sm-8">
                                              <?= $language_info['action'] ?>
                                          </div>
                                            
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (isset($invoice_info)) {
                                            echo form_hidden('merge_current_invoice', $invoice_info->invoices_id);
                                            echo form_hidden('isedit', $invoice_info->invoices_id);
                                        }
                                        ?>
                                        <tr class="main">                                           
                                            <td>
                                            <div class="row">
                                             <div class="col-lg-2 col-sm-8">
                                             <label class="formobile">Item Name: </label>  
                        <textarea name="item_name" class="form-control"
                                  placeholder="<?php echo lang('item_name'); ?>"></textarea>
                                  </div>
                                   <div class="col-lg-2 col-sm-8">
                                     <label class="formobile">Description: </label>      
                        <textarea name="item_desc" class="form-control"
                                  placeholder="<?php echo lang('description'); ?>"></textarea>
                                            </div>
                                            <?php
                                            $invoice_view = config_item('invoice_view');
                                            if (!empty($invoice_view) && $invoice_view == '2') {
                                                ?>
                                                 <div class="col-lg-2 col-sm-8">
                                                 <input type="text" name="hsn_code"
                                                           class="form-control"></div>
                                            <?php } ?>
                                             <div class="col-lg-1 col-sm-8">
                                              <label class="formobile">Qty/ Hours:</label>
                                                <input type="text" data-parsley-type="number" name="quantity" min="0"
                                                       value="1"
                                                       class="form-control"
                                                       placeholder="<?php echo lang('qty'); ?>">

                                                <input type="hidden"
                                                       placeholder="<?php echo lang('unit') . ' ' . lang('type'); ?>"
                                                       name="unit"
                                                       class="form-control input-transparent">
                                            </div>
                                            <div class="col-lg-2 col-sm-8">
                                             <label class="formobile">Price:</label>
                                                <input type="hidden" name="new_itmes_id" class="form-control">
                                                <input type="text" data-parsley-type="number" name="unit_cost"
                                                       class="form-control"
                                                       placeholder="<?php echo lang('price'); ?>">
                                            </div>
                                             <div class="col-lg-2 col-sm-8">
                                             <label class="formobile">Tax Rate:</label>
                                             <div style="width: 100% !important;">
                                                <?php
                                                $taxes = $this->db->order_by('tax_rate_percent', 'ASC')->get('tbl_tax_rates')->result();
                                                $default_tax = config_item('default_tax');
                                                if (!is_numeric($default_tax)) {
                                                    $default_tax = unserialize($default_tax);
                                                }
                                                $select = '<select class="selectpicker tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . lang('no_tax') . '">';
                                                foreach ($taxes as $tax) {
                                                    $selected = '';
                                                    if (!empty($default_tax) && is_array($default_tax)) {
                                                        if (in_array($tax->tax_rates_id, $default_tax)) {
                                                            $selected = ' selected ';
                                                        }
                                                    }
                                                    $select .= '<option value="' . $tax->tax_rate_name . '|' . $tax->tax_rate_percent . '"' . $selected . 'data-taxrate="' . $tax->tax_rate_percent . '" data-taxname="' . $tax->tax_rate_name . '" data-subtext="' . $tax->tax_rate_name . '">' . $tax->tax_rate_percent . '%</option>';
                                                }
                                                $select .= '</select>';
                                                echo $select;
                                                ?>
                                                </div>
                                            </div>
                                           <div class="col-lg-2 col-sm-8">
                                            <label class="formobile">Total:</label>
                                           &nbsp;</div>
                                            
                                           <div class="col-lg-1 col-sm-8">
                                           <label class="formobile">Action:</label>
                                                <?php
                                                $new_item = 'undefined';
                                                if (isset($invoice_info)) {
                                                    $new_item = true;
                                                }
                                                ?>
                                                <div style="width: 100% !important;">
                                                <button type="button"
                                                        onclick="add_item_to_table2('undefined','undefined',<?php echo $new_item; ?>); return false;"
                                                        class="btn-xs btn btn-info"><i class="fa fa-check"></i>
                                                </button>
                                                </div>
                                            </div>
                                            </td>
                                        </tr>
                                        <?php if (isset($invoice_info) || isset($add_items)) {
                                            $i = 1;
                                            $items_indicator = 'items';
                                            if (isset($invoice_info)) {
                                                $add_items = $this->invoice_model->ordered_items_by_id($invoice_info->invoices_id);
                                                $items_indicator = 'items';
                                            }

                                            foreach ($add_items as $item) {
                                                $manual = false;
                                                $table_row = '<tr class="sortable item">';
                                                $table_row .= '<td class="dragger">';
                                                if (!is_numeric($item->quantity)) {
                                                    $item->quantity = 1;
                                                }
                                                $invoice_item_taxes = $this->invoice_model->get_invoice_item_taxes($item->items_id);

                                                // passed like string
                                                if ($item->items_id == 0) {
                                                    $invoice_item_taxes = $invoice_item_taxes[0];
                                                    $manual = true;
                                                }
                                                $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][items_id]', $item->items_id);
                                                $amount = $item->unit_cost * $item->quantity;
                                                $amount = ($amount);
                                                // order input
                                                $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]"><input type="hidden" name="items_id[]" value="' . $item->items_id . '"><input type="hidden" name="saved_items_id[]" value="' . $item->saved_items_id . '">';
                                                $table_row .= '</td>';
                                                $table_row .= '<td class="bold item_name"><textarea name="' . $items_indicator . '[' . $i . '][item_name]" class="form-control">' . $item->item_name . '</textarea></td>';
                                                $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][item_desc]" class="form-control" >' . $item->item_desc . '</textarea></td>';
                                                $invoice_view = config_item('invoice_view');
                                                if (!empty($invoice_view) && $invoice_view == '2') {
                                                    $table_row .= '<td><input type="text" name="' . $items_indicator . '[' . $i . '][hsn_code]" class="form-control" value="' . $item->hsn_code . '"></td>';
                                                }
                                                $table_row .= '<td><input type="text" data-parsley-type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][quantity]" value="' . $item->quantity . '" class="form-control">';
                                                $unit_placeholder = '';
                                                if (!$item->unit) {
                                                    $unit_placeholder = lang('unit');
                                                    $item->unit = '';
                                                }
                                                $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item->unit . '">';
                                                $table_row .= '</td>';
                                                $table_row .= '<td class="rate"><input type="text" data-parsley-type="number" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][unit_cost]" value="' . $item->unit_cost . '" class="form-control"></td>';
                                                $table_row .= '<td class="taxrate">' . $this->admin_model->get_taxes_dropdown('' . $items_indicator . '[' . $i . '][taxname][]', $invoice_item_taxes, 'invoice', $item->items_id, true, $manual) . '</td>';
                                                $table_row .= '<td class="amount">' . $amount . '</td>';
                                                $table_row .= '<td><a href="#" class="btn-xs btn btn-danger pull-left" onclick="delete_item(this,' . $item->items_id . '); return false;"><i class="fa fa-trash"></i></a></td>';
                                                $table_row .= '</tr>';
                                                echo $table_row;
                                                $i++;
                                            }
                                        }
                                        ?>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-xs-8 pull-right">
                                        <table class="table text-right">
                                            <tbody>
                                            <tr id="subtotal">
                                                <td><span class="bold"><?php echo lang('sub_total'); ?> :</span>
                                                </td>
                                                <td class="subtotal">
                                                </td>
                                            </tr>
                                            <tr id="discount_percent">
                                                <td>
                                                    <div class="row">
                                                        <div class="col-md-7">
                                                            <span class="bold"><?php echo lang('discount'); ?>
                                                                (%)</span>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <?php
                                                            $discount_percent = 0;
                                                            if (isset($invoice_info)) {
                                                                if ($invoice_info->discount_percent != 0) {
                                                                    $discount_percent = $invoice_info->discount_percent;
                                                                }
                                                            }
                                                            ?>
                                                            <input type="text" data-parsley-type="number"
                                                                   value="<?php echo $discount_percent; ?>"
                                                                   class="form-control pull-left" min="0" max="100"
                                                                   name="discount_percent">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="discount_percent"></td>
                                            </tr>
                                            <tr style="display:none;">
                                                <td>
                                                    <div class="row">
                                                        <div class="col-md-7">
                                                            <span class="bold"><?php echo lang('adjustment'); ?></span>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" data-parsley-type="number"
                                                                   value="<?php if (isset($invoice_info)) {
                                                                       echo $invoice_info->adjustment;
                                                                   } else {
                                                                       echo 0;
                                                                   } ?>" class="form-control pull-left"
                                                                   name="adjustment">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="adjustment"></td>
                                            </tr>
                                            <tr>
                                                <td><span class="bold"><?php echo lang('total'); ?> :</span>
                                                </td>
                                                <td class="total">
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="removed-items"></div>
                                
                                 <div class="col-sm-12">
                                    <div class="">
                                        <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                                        <div class="col-lg-11 row">
                        <textarea name="notes" class="textarea_"><?php
                            if (!empty($invoice_info)) {
                                echo $invoice_info->notes;
                            } else {
                                echo $this->config->item('default_terms');
                            }
                            ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <input type="submit" value="<?= lang('save_as_draft') ?>" name="save_as_draft"
                                           class="btn btn-primary">
                                    <input type="submit" value="<?= lang('update') ?>" name="update"
                                           class="btn btn-success">
                                </div>
                            </div>
</form>
<?php } else { ?>
    </div>
<?php } ?>
</div>

<script type="text/javascript">
    function slideToggle($id) {
        $('#quick_state').attr('data-original-title', '<?= lang('view_quick_state') ?>');
        $($id).slideToggle("slow");
    }

    $(document).ready(function () {
        $("#select_all_tasks").click(function () {
            $(".tasks_list").prop('checked', $(this).prop('checked'));
        });
        $("#select_all_expense").click(function () {
            $(".expense_list").prop('checked', $(this).prop('checked'));
        });
        $('[data-toggle="popover"]').popover();

    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        init_items_sortable();
        $('#start_recurring').click(function () {
            $('#recurring').slideToggle("fast");
            $('#recurring').removeClass("hide");
            $('#recuring_frequency').prop('disabled', false);
        });
    });
</script>
