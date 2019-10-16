<?php
global $WCMp_Advance_Shipping;
$conditions = array('' => __('None', 'wcmp-advance-shipping'), 'price' => __('Price', 'wcmp-advance-shipping'), 'weight' => __('Weight', 'wcmp-advance-shipping'), 'items' => __('Item count', 'wcmp-advance-shipping'));

$index = !empty( $option->rate_id ) ? $option->rate_id : 0;
?>
<tr>
    <td class="table-rate-item-select" style="vertical-align: middle; text-align: center;">
        <input type="checkbox" name="wcmp_table_rate[<?php echo $index; ?>]['selected_rate_id']" data-name="selected_rate_id" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_id ?>" />
        <input type="hidden" name="wcmp_table_rate[<?php echo $index; ?>]['rate_id']" data-name="rate_id" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_id ?>" />
    </td>
    <td>
        <select name="wcmp_table_rate[<?php echo $index; ?>]['rate_condition']" onchange="toggleDisableRate(this)" data-name="rate_condition" data-instance_id="<?php echo $shipping_method_id; ?>" class="wcmp_table_rate_condition form-control">
            <?php
            foreach ($conditions as $key => $condition) {
                if ($key == $option->rate_condition) {
                    echo '<option value="' . $key . '" selected="">' . $condition . '</option>';
                } else {
                    echo '<option value="' . $key . '">' . $condition . '</option>';
                }
            }
            ?>
        </select>
    </td>
    <td>
        <input type="text" class="form-control" data-name="rate_min" data-instance_id="<?php echo $shipping_method_id; ?>" <?php if (empty($option->rate_condition)) echo 'disabled=""'; ?> value="<?php echo $option->rate_min; ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_min']" /> 
    </td>
    <td>
        <input type="text" class="form-control" data-name="rate_max" data-instance_id="<?php echo $shipping_method_id; ?>" <?php if (empty($option->rate_condition)) echo 'disabled=""'; ?> value="<?php echo $option->rate_max; ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_max']" />
    </td>
    <td style="vertical-align: middle; text-align: center;">
    	<input type="checkbox" name="wcmp_table_rate[<?php echo $index; ?>]['rate_priority']" data-name="rate_priority" data-instance_id="<?php echo $shipping_method_id; ?>" value="1" <?php if($option->rate_priority) echo "checked"; ?> />
    </td>
    <td style="vertical-align: middle; text-align: center;">
    	<input type="checkbox" name="wcmp_table_rate[<?php echo $index; ?>]['rate_abort']" data-name="rate_abort" data-instance_id="<?php echo $shipping_method_id; ?>" value="1" <?php if($option->rate_abort) echo "checked"; ?>/>
    
    </td>
	<td colspan="4" class="abort_reason" <?php if(!$option->rate_abort) echo 'style="display:none;"';?> >
		<input type="text" class="form-control full-width" data-name="rate_abort_reason" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php if (isset($option->rate_abort_reason) && !empty($option->rate_abort_reason)) echo $option->rate_abort_reason ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_abort_reason']" />
	</td>
	<td class="cost" <?php if($option->rate_abort) echo 'style="display:none;"';?> >
		<input type="text" class="form-control" data-name="rate_cost" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_cost ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_cost']" />
	</td>
	<td class="cost" <?php if($option->rate_abort) echo 'style="display:none;"';?> >
		<input type="text" class="form-control" data-name="rate_cost_per_item" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_cost_per_item ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_cost_per_item']" />
	</td>
	<td class="cost" <?php if($option->rate_abort) echo 'style="display:none;"';?> >
		<input type="text" class="form-control" data-name="rate_cost_per_weight_unit" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_cost_per_weight_unit ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_cost_per_weight_unit']" />
	</td>
	<td class="cost" <?php if($option->rate_abort) echo 'style="display:none;"';?> >
		<input type="text" class="form-control" data-name="rate_cost_percent" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_cost_percent ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_cost_percent']" />
	</td>
    <td>
        <input type="text" class="form-control" data-name="rate_label" data-instance_id="<?php echo $shipping_method_id; ?>" value="<?php echo $option->rate_label ?>" name="wcmp_table_rate[<?php echo $index; ?>]['rate_label']" />
    </td>
</tr>