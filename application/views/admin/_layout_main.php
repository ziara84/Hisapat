<?php $this->load->view('admin/components/htmlheader');
$opened = $this->session->userdata('opened');
$this->session->unset_userdata('opened');
?>
<script>
    function startTime() {
        var time = new Date();
        var date = time.getDate();
        var month = time.getMonth() + 1;
        var years = time.getFullYear();
        var hr = time.getHours();
        var hour = time.getHours();
        var min = time.getMinutes();
        var minn = time.getMinutes();
        var sec = time.getSeconds();
        var secc = time.getSeconds();
        if (date <= 9) {
            var dates = "0" + date;
        } else {
            dates = date;
        }
        if (month <= 9) {
            var months = "0" + month;
        } else {
            months = month;
        }
        var ampm = " PM "
        if (hr < 12) {
            ampm = " AM "
        }
        if (hr > 12) {
            hr -= 12
        }
        if (hr < 10) {
            hr = " " + hr
        }
        if (min < 10) {
            min = "0" + min
        }
        if (sec < 10) {
            sec = "0" + sec
        }
        document.getElementById('date').value = years + "-" + months + "-" + dates;
        document.getElementById('clock_time').value = hour + ":" + minn + ":" + secc;
        document.getElementById('txt').innerHTML = hr + ":" + min + ":" + sec + ampm;
        var t = setTimeout(function () {
            startTime()
        }, 500);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }
        ;  // add zero in front of numbers < 10
        return i;
    }

</script>
<body onLoad="startTime()" class="<?php if (!empty($opened)) {
    echo 'offsidebar-open';
} ?> <?= config_item('aside-float') . ' ' . config_item('aside-collapsed') . ' ' . config_item('layout-boxed') . ' ' . config_item('layout-fixed') ?>">
<div class="wrapper">
    <!-- top navbar-->
    <?php $this->load->view('admin/components/header'); ?>
    <!-- sidebar-->
    <?php $this->load->view('admin/components/sidebar'); ?>
    <!-- offsidebar-->
    <?php $this->load->view('admin/components/offsidebar'); ?>
    <!-- Main section-->

    <section>
        <!--        <div id="pre-loader">-->
        <!--            <div id="pre-loade" class="app-loader">-->
        <!--                <div class="loading"></div>-->
        <!--            </div>-->
        <!--        </div>-->
        <?php
        $active_pre_loader = config_item('active_pre_loader');
        if (!empty($active_pre_loader) && $active_pre_loader == 1) {
            ?>
            <div id="loader-wrapper">
                <div id="loader"></div>
            </div>
        <?php } ?>
        <!-- Page content-->
        <div class="content-wrapper">


            <!--            start heading-->
            <div class="content-heading">
                <div class="row">
                <div class="col-md-4">
                    <?php echo $this->breadcrumbs->build_breadcrumbs();
                    $user_id = $this->session->userdata('user_id');
                    $attendance_info = $this->db->where('user_id', $user_id)->get('tbl_attendance')->result();

                    foreach ($attendance_info as $v_info) {
                        $all_clocking[] = $this->admin_model->check_by(array('attendance_id' => $v_info->attendance_id, 'clocking_status' => 1), 'tbl_clock');
                    }
                    if (!empty($all_clocking)) {
                        foreach ($all_clocking as $v_clocking) {
                            if (!empty($v_clocking)) {
                                $clocking = $v_clocking;
                            }
                        }
                    }

                    ?>
                </div>
                    <div >

                    <div class="col-md-8" style="text-align: right;">

                        <form method="post" action="<?php echo base_url() ?>admin/dashboard/set_clocking/<?php
                        if (!empty($clocking)) {
                            echo $clocking->clock_id;
                        }
                        ?>">

                                <div class=" col-md-offset-4 col-md-6" style="text-align: right;">
                                <small class="text-sm"> &nbsp;<?php echo date('l jS F \- Y,'); ?>
                                    &nbsp;<?= lang('time') ?>
                                    &nbsp;<span
                                            id="txt"></span></small>
                                </div>

                                <div class="col-md-2" style="text-align: right;">

                                <input type="hidden" name="clock_date" value="" id="date">
                                <input type="hidden" name="clock_time" value="" id="clock_time">
                                <?php if (!empty($clocking->clock_id)): ?>
                                    <button name="clocktime" type="submit" id="sbtn" value="2"
                                            class="btn btn-danger clock_in_button"><i
                                                class="fa fa-arrow-left"></i> <?= lang('clock_out') ?>
                                    </button>
                                <?php else: ?>
                                    <button name="clocktime" type="submit" id="sbtn" value="1"
                                            class="btn btn-success clock_in_button"><i
                                                class="fa fa-sign-out"></i> <?= lang('clock_in') ?>
                                    </button>
                                <?php endif; ?>
                            </div>

                        </form>
                    </div>
                </div>
                </div>
            </div>

            <!--            end heading-->


            <div class="row">
                <div class="col-lg-12">
                    <?php echo $subview ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Page footer-->

    <footer>
        <div class="pull-right hidden-xs">
            <?= '<b>' . lang('version') . '</b> ' . config_item('version') ?>
        </div>
       <strong>&copy; <a href="https://fekrasoftec.com">Fekra Softowers</a>.</strong>
        All rights reserved.
    </footer>
</div>
<?php
$this->load->view('admin/components/footer');
$direction = $this->session->userdata('direction');
if (!empty($direction) && $direction == 'rtl') {
    $RTL = 'on';
} else {
    $RTL = config_item('RTL');
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("button[name$='clocktime']").click(function () {
            var ubtn = $(this);
            ubtn.html('Please wait...');
            ubtn.addClass('disabled');
        });

        $('[data-ui-slider]').slider({
            <?php
            if (!empty($RTL)) {?>
            reversed: true,
            <?php }
            ?>
        });
        /*
         * Multiple drop down select
         */
        $(".select_box").select2({
            <?php

            if (!empty($RTL)) {?>
            dir: "rtl",
            <?php }
            ?>
        });
        $(".select_2_to").select2({
            tags: true,
            <?php
            if (!empty($RTL)) {?>
            dir: "rtl",
            <?php }
            ?>
            allowClear: true,
            placeholder: 'To : Select or Write',
            tokenSeparators: [',', ' ']
        });
        $(".select_multi").select2({
            tags: true,
            <?php
            if (!empty($RTL)) {?>
            dir: "rtl",
            <?php }
            ?>
            allowClear: true,
            placeholder: 'Select Multiple',
            tokenSeparators: [',', ' ']
        });
    })
</script>

<script type="text/javascript">

    $(document).ready(function () {
        $('.complete input[type="checkbox"]').change(function () {
            var task_id = $(this).data().id;
            var task_complete = $(this).is(":checked");

            var formData = {
                'task_id': task_id,
                'task_progress': 100,
                'task_status': 'completed'
            };
            $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: '<?= base_url()?>admin/tasks/completed_tasks/' + task_id, // the url where we want to POST
                data: formData, // our data object
                dataType: 'json', // what type of data do we expect back from the server
                encode: true,
                success: function (res) {
                    console.log(res);
                    if (res) {
                        toastr[res.status](res.message);
                    } else {
                        alert('There was a problem with AJAX');
                    }
                }
            })

        });

    })
    ;
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#permission_user_1').hide();
        $("div.action_1").hide();
        $("input[name$='permission']").click(function () {
            $("#permission_user_1").removeClass('show');
            if ($(this).attr("value") == "custom_permission") {
                $("#permission_user_1").show();
            } else {
                $("#permission_user_1").hide();
            }
        });
        $("input[name$='assigned_to[]']").click(function () {
            var user_id = $(this).val();
            $("#action_1" + user_id).removeClass('show');
            if (this.checked) {
                $("#action_1" + user_id).show();
            } else {
                $("#action_1" + user_id).hide();
            }

        });
    });

</script>

<?php $this->load->view('admin/_layout_modal'); ?>
<?php $this->load->view('admin/_layout_modal_lg'); ?>
<?php $this->load->view('admin/_layout_modal_large'); ?>
<?php $this->load->view('admin/_layout_modal_extra_lg'); ?>
