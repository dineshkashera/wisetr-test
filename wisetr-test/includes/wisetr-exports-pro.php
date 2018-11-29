<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $title;

$exports_fields  = Wisetr_Test::get_all_post_meta_key();
?>
<div id="wpbody-content" aria-label="Main content" tabindex="0">
    <div class="wrap woocommerce">
    		<h2><?php echo $title;?></h2>
    		<form method="post" action="">
            	<table class="form-table">
                	<tbody>
                		<tr valign="top" class="single_select_page">
                			<th scope="row" class="titledesc">
                				<label>Select Products Fields <span class="woocommerce-help-tip"></span></label>
                			</th>
                			<td class="forminp">
                				<select name="woo_product_export[]" class="woo_product_export_cls" style="min-width:300px;" id="woo_product_export" multiple="multiple">
                                	<?php 
                                	foreach($exports_fields as $key => $field_name){?>
                                	    <option value="<?php echo $field_name;?>"><?php echo ucwords(str_replace('_',' ',$field_name));?> </option>
                                	<?php } ?>
                                </select>
                				<span class="description"><br>Select Fields for exports</span>							
                			</td>
                		</tr>
                	</tbody>
            	</table>		
            	<p class="submit">
            		<button name="wisetr_export" class="button-primary woocommerce-save-button" type="submit" value="Export">Export</button>
            	</p>
        	</form>
   	</div>
	<div class="clear"></div>	
</div>