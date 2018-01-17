<?php

class Invoice_Model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;

    public function get_payment_status($invoice_id)
    {

        $tax = $this->get_invoice_tax_amount($invoice_id);
        $discount = $this->get_invoice_discount($invoice_id);
        $invoice_cost = $this->get_invoice_cost($invoice_id);
        $payment_made = round($this->get_invoice_paid_amount($invoice_id), 2);
        $due = round(((($invoice_cost - $discount) + $tax) - $payment_made));
        $invoice_info = $this->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        if ($invoice_info->status == 'draft') {
            return lang('draft');
        } elseif ($invoice_info->status == 'Cancelled') {
            return lang('cancelled');
        } elseif ($payment_made < 1) {
            return lang('not_paid');
        } elseif ($due <= 0) {
            return lang('fully_paid');
        } else {
            return lang('partially_paid');
        }
    }

    public function invoice_perc($invoice)
    {
        $invoice_payment = $this->invoice_payment($invoice);
        $invoice_payable = $this->invoice_payable($invoice);
        if ($invoice_payable < 1 OR $invoice_payment < 1) {
            $perc_paid = 0;
        } else {
            $perc_paid = ($invoice_payment / $invoice_payable) * 100;
        }
        return round($perc_paid);
    }

    public function invoice_payment($invoice)
    {
        $this->ci->db->where('invoice', $invoice);
        $this->ci->db->select_sum('amount');
        $query = $this->ci->db->get('payments');
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->amount;
        }
    }

    function ordered_items_by_id($id, $type = 'invoices')
    {
        $table = ($type == 'invoices' ? '' : 'estimate_') . 'tbl_items';
        $result = $this->db->where($type . '_id', $id)->order_by('order', 'asc')->get($table)->result();
        return $result;
    }

    function calculate_to($invoice_value, $invoice_id)
    {
        switch ($invoice_value) {
            case 'invoice_cost':
                return $this->get_invoice_cost($invoice_id);
                break;
            case 'tax':
                return $this->get_invoice_tax_amount($invoice_id);
                break;
            case 'discount':
                return $this->get_invoice_discount($invoice_id);
                break;
            case 'paid_amount':
                return $this->get_invoice_paid_amount($invoice_id);
                break;
            case 'invoice_due':
                return $this->get_invoice_due_amount($invoice_id);
                break;
            case 'total':
                return $this->get_invoice_total_amount($invoice_id);
                break;
        }
    }

    function get_invoice_cost($invoice_id)
    {
        $this->db->select_sum('total_cost');
        $this->db->where('invoices_id', $invoice_id);
        $this->db->from('tbl_items');
        $query_result = $this->db->get();
        $cost = $query_result->row();
        if (!empty($cost->total_cost)) {
            $result = $cost->total_cost;
        } else {
            $result = '0';
        }
        return $result;
    }


    public function get_invoice_tax_amount($invoice_id)
    {
        $invoice_info = $this->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $tax_info = json_decode($invoice_info->total_tax);
        $tax = 0;
        if (!empty($tax_info)) {
            $total_tax = $tax_info->total_tax;
            if (!empty($total_tax)) {
                foreach ($total_tax as $t_key => $v_tax_info) {
                    $tax += $v_tax_info;
                }
            }
        }
        return $tax;
    }

    public function get_invoice_discount($invoice_id)
    {
        $invoice_info = $this->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        return $invoice_info->discount_total;

    }

    public function get_invoice_paid_amount($invoice_id)
    {

        $this->db->select_sum('amount');
        $this->db->where('invoices_id', $invoice_id);
        $this->db->from('tbl_payments');
        $query_result = $this->db->get();
        $amount = $query_result->row();
        $tax = $this->get_invoice_tax_amount($invoice_id);
        if (!empty($amount->amount)) {
            $result = $amount->amount;
        } else {
            $result = '0';
        }
        return $result;
    }

    public function get_invoice_due_amount($invoice_id)
    {

        $invoice_info = $this->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $tax = $this->get_invoice_tax_amount($invoice_id);
        $discount = $this->get_invoice_discount($invoice_id);
        $invoice_cost = $this->get_invoice_cost($invoice_id);
        $payment_made = $this->get_invoice_paid_amount($invoice_id);
        $due_amount = (($invoice_cost - $discount) + $tax) - $payment_made + $invoice_info->adjustment;
        if ($due_amount <= 0) {
            $due_amount = 0;
        }
        return $due_amount;
    }

    public function get_invoice_total_amount($invoice_id)
    {

        $invoice_info = $this->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $tax = $this->get_invoice_tax_amount($invoice_id);
        $discount = $this->get_invoice_discount($invoice_id);
        $invoice_cost = $this->get_invoice_cost($invoice_id);
        $payment_made = $this->get_invoice_paid_amount($invoice_id);

        $total_amount = $invoice_cost - $discount + $tax + $invoice_info->adjustment;
        if ($total_amount <= 0) {
            $total_amount = 0;
        }
        return $total_amount;
    }

    function all_invoice_amount()
    {
        $invoices = $this->db->get('tbl_invoices')->result();
        $cost[] = array();
        foreach ($invoices as $invoice) {
            $tax = round($this->get_invoice_tax_amount($invoice->invoices_id));
            $discount = round($this->get_invoice_discount($invoice->invoices_id));
            $invoice_cost = round($this->get_invoice_cost($invoice->invoices_id));

            $cost[] = ($invoice_cost + $tax) - $discount;
        }
        if (is_array($cost)) {
            return round(array_sum($cost), 2);
        } else {
            return 0;
        }
    }

    function all_outstanding()
    {
        $invoices = $this->db->get('tbl_invoices')->result();
        $due[] = array();
        foreach ($invoices as $invoice) {
            $due[] = $this->get_invoice_due_amount($invoice->invoices_id);
        }
        if (is_array($due)) {
            return round(array_sum($due), 2);
        } else {
            return 0;
        }
    }

    function client_outstanding($client_id, $project_id = null)
    {
        $due[] = array();
        if (!empty($project_id)) {
            $invoices_info = $this->db->where('project_id', $project_id)->get('tbl_invoices')->result();
        } else {
            $invoices_info = $this->db->where('client_id', $client_id)->get('tbl_invoices')->result();
        }

        foreach ($invoices_info as $v_invoice) {
            $due[] = $this->get_invoice_due_amount($v_invoice->invoices_id);
        }
        if (is_array($due)) {
            return round(array_sum($due), 2);
        } else {
            return 0;
        }
    }

    public function check_for_merge_invoice($client_id, $current_invoice)
    {

        $invoice_info = $this->db->where('client_id', $client_id)->get('tbl_invoices')->result();

        foreach ($invoice_info as $v_invoice) {
            if ($v_invoice->invoices_id != $current_invoice) {
                $payment_status = $this->get_payment_status($v_invoice->invoices_id);
                if ($payment_status == lang('not_paid') || $payment_status == lang('draft')) {
                    $invoice[] = $v_invoice;
                }
            }
        }
        if (!empty($invoice)) {
            return $invoice;
        } else {
            return array();
        }
    }

    public function get_invoice_filter()
    {
        $all_invoice = $this->get_permission('tbl_invoices');
        if (!empty($all_invoice)) {
            $all_invoice = array_reverse($all_invoice);
            foreach ($all_invoice as $v_invoices) {
                $year[] = date('Y', strtotime($v_invoices->invoice_date));
            }
        }
        if (!empty($year)) {
            $result = array_unique($year);
        }

        $statuses = array(
            array(
                'id' => 1,
                'value' => 'paid',
                'name' => lang('paid'),
                'order' => 1,
            ),
            array(
                'id' => 2,
                'value' => 'not_paid',
                'name' => lang('not_paid'),
                'order' => 2,
            ),
            array(
                'id' => 3,
                'value' => 'partially_paid',
                'name' => lang('partially_paid'),
                'order' => 3,
            ),
            array(
                'id' => 1,
                'value' => 'draft',
                'name' => lang('draft'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'cancelled',
                'name' => lang('cancelled'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'overdue',
                'name' => lang('overdue'),
                'order' => 1,
            ),
            array(
                'id' => 4,
                'value' => 'recurring',
                'name' => lang('recurring'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'last_month',
                'name' => lang('last_month'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'this_months',
                'name' => lang('this_months'),
                'order' => 4,
            )
        );
        if (!empty($result)) {
            foreach ($result as $v_year) {
                $test = array(
                    'id' => 1,
                    'value' => '_' . $v_year,
                    'name' => $v_year,
                    'order' => 1);
                if (!empty($test)) {
                    array_push($statuses, $test);
                }
            }
        }
        return $statuses;
    }

    // Get a list of recurring invoices
    public function recurring_invoices($client_id = null)
    {
        if (!empty($client_id)) {
            return $this->db->where(array('recurring' => 'Yes', 'client_id' => $client_id))->get('tbl_invoices')->result();
        } else {
            return $this->db->where(array('recurring' => 'Yes', 'invoices_id >' => 0))->get('tbl_invoices')->result();
        }
    }

    public function get_invoices($filterBy = null, $client_id = null)
    {
        if (!empty($client_id)) {
            $all_invoice = $this->db->where('client_id', $client_id)->get('tbl_invoices')->result();
        } else {
            $all_invoice = $this->get_permission('tbl_invoices');
        }
        if (empty($filterBy) || !empty($filterBy) && $filterBy == 'all') {
            return $all_invoice;
        } elseif ($filterBy == 'recurring') {
            return $this->recurring_invoices($client_id);
        } else {
            if (!empty($all_invoice)) {
                $all_invoice = array_reverse($all_invoice);
                foreach ($all_invoice as $v_invoices) {
                    if ($filterBy == 'paid') {
                        if ($this->get_payment_status($v_invoices->invoices_id) == lang('fully_paid')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'not_paid') {
                        if ($this->get_payment_status($v_invoices->invoices_id) == lang('not_paid')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'draft') {
                        if ($this->get_payment_status($v_invoices->invoices_id) == lang('draft')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'partially_paid') {
                        if ($this->get_payment_status($v_invoices->invoices_id) == lang('partially_paid')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'cancelled') {
                        if ($this->get_payment_status($v_invoices->invoices_id) == lang('cancelled')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'overdue') {
                        $payment_status = $this->get_payment_status($v_invoices->invoices_id);
                        if (strtotime($v_invoices->due_date) < time() AND $payment_status != lang('fully_paid')) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                        if ($filterBy == 'last_month') {
                            $month = date('Y-m', strtotime('-1 months'));
                        } else {
                            $month = date('Y-m');
                        }
                        if (strtotime($v_invoices->invoice_month) == strtotime($month)) {
                            $invoice[] = $v_invoices;
                        }
                    } else if (strstr($filterBy, '_')) {
                        $year = str_replace('_', '', $filterBy);
                        if (strtotime($v_invoices->invoice_year) == strtotime($year)) {
                            $invoice[] = $v_invoices;
                        }
                    }

                }
            }
        }
        if (!empty($invoice)) {
            return $invoice;
        } else {
            return array();
        }
    }

    public function get_client_wise_invoice()
    {
        $all_invoice = $this->get_permission('tbl_invoices');
        $client_invoice = array();
        if (!empty($all_invoice)) {
            $all_invoice = array_reverse($all_invoice);
            foreach ($all_invoice as $v_invoices) {
                $due = $this->calculate_to('invoice_due', $v_invoices->invoices_id);
                if ($due != 0) {
                    $client_invoice[$v_invoices->client_id][] = $v_invoices;
                }
            }
            return $client_invoice;
        }
    }

    public function get_invoice_payment()
    {
        $all_invoice = $this->db->get('tbl_payments')->result();
        $all_method = $this->db->get('tbl_payment_methods')->result();
        if (!empty($all_invoice)) {
            $all_invoice = array_reverse($all_invoice);
            foreach ($all_invoice as $v_invoices) {
                $years[] = $v_invoices->year_paid;
            }
        }
        if (!empty($years)) {
            $result = array_unique($years);
        }

        $statuses = array(
            array(
                'id' => 4,
                'value' => 'last_month',
                'name' => lang('last_month'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'this_months',
                'name' => lang('this_months'),
                'order' => 4,
            )
        );
        if (!empty($result)) {
            foreach ($result as $v_year) {
                $year = array(
                    'id' => 1,
                    'value' => '_' . $v_year,
                    'name' => $v_year,
                    'order' => 1);
                if (!empty($year)) {
                    array_push($statuses, $year);
                }
            }
        }
        if (!empty($all_method)) {
            foreach ($all_method as $v_method) {
                $method = array(
                    'id' => 1,
                    'value' => $v_method->payment_methods_id,
                    'name' => $v_method->method_name,
                    'order' => 1);
                if (!empty($method)) {
                    array_push($statuses, $method);
                }
            }
        }
        return $statuses;
    }

    public function get_payments($filterBy = null, $client_id = null)
    {
        if (!empty($client_id)) {
            $all_payments = $this->db->where('paid_by', $client_id)->get('tbl_payments')->result();
        } else {
            $all_payments = $this->db->get('tbl_payments')->result();
        }
        if (empty($filterBy) || !empty($filterBy) && $filterBy == 'all') {
            return $all_payments;
        } else {
            if (!empty($all_payments)) {
                foreach ($all_payments as $v_payments) {
                    if (is_numeric($filterBy)) {
                        if ($v_payments->payment_method == $filterBy) {
                            $payment[] = $v_payments;
                        }
                    } else if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                        if ($filterBy == 'last_month') {
                            $month = date('m', strtotime('-1 months'));
                        } else {
                            $month = date('m');
                        }
                        if ($v_payments->month_paid == $month) {
                            $payment[] = $v_payments;
                        }
                    } else if (strstr($filterBy, '_')) {
                        $year = str_replace('_', '', $filterBy);
                        if (strtotime($v_payments->year_paid) == strtotime($year)) {
                            $payment[] = $v_payments;
                        }
                    }

                }
            }
        }

        if (!empty($payment)) {
            return $payment;
        } else {
            return array();
        }
    }


}
