<?php

/**
 * This is the model class for table "sales_flat_quote_item".
 *
 * The followings are the available columns in table 'sales_flat_quote_item':
 * @property string $item_id
 * @property string $quote_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $product_id
 * @property integer $store_id
 * @property string $parent_item_id
 * @property integer $is_virtual
 * @property string $sku
 * @property string $name
 * @property string $description
 * @property string $applied_rule_ids
 * @property string $additional_data
 * @property integer $free_shipping
 * @property integer $is_qty_decimal
 * @property integer $no_discount
 * @property string $weight
 * @property string $qty
 * @property string $price
 * @property string $base_price
 * @property string $custom_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $base_discount_amount
 * @property string $tax_percent
 * @property string $tax_amount
 * @property string $base_tax_amount
 * @property string $row_total
 * @property string $base_row_total
 * @property string $row_total_with_discount
 * @property string $row_weight
 * @property string $product_type
 * @property string $base_tax_before_discount
 * @property string $tax_before_discount
 * @property string $original_custom_price
 * @property string $redirect_url
 * @property string $base_cost
 * @property string $price_incl_tax
 * @property string $base_price_incl_tax
 * @property string $row_total_incl_tax
 * @property string $base_row_total_incl_tax
 * @property string $hidden_tax_amount
 * @property string $base_hidden_tax_amount
 * @property integer $gift_message_id
 * @property string $weee_tax_disposition
 * @property string $weee_tax_row_disposition
 * @property string $base_weee_tax_disposition
 * @property string $base_weee_tax_row_disposition
 * @property string $weee_tax_applied
 * @property string $weee_tax_applied_amount
 * @property string $weee_tax_applied_row_amount
 * @property string $base_weee_tax_applied_amount
 * @property string $base_weee_tax_applied_row_amnt
 * @property integer $event_id
 * @property integer $giftregistry_item_id
 * @property integer $gw_id
 * @property string $gw_base_price
 * @property string $gw_price
 * @property string $gw_base_tax_amount
 * @property string $gw_tax_amount
 */
class Mage1SalesQuoteItem extends Mage1ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{sales_flat_quote_item}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('created_at', 'required'),
			array('store_id, is_virtual, free_shipping, is_qty_decimal, no_discount, gift_message_id, event_id, giftregistry_item_id, gw_id', 'numerical', 'integerOnly'=>true),
			array('quote_id, product_id, parent_item_id', 'length', 'max'=>10),
			array('sku, name, product_type, redirect_url', 'length', 'max'=>255),
			array('weight, qty, price, base_price, custom_price, discount_percent, discount_amount, base_discount_amount, tax_percent, tax_amount, base_tax_amount, row_total, base_row_total, row_total_with_discount, row_weight, base_tax_before_discount, tax_before_discount, original_custom_price, base_cost, price_incl_tax, base_price_incl_tax, row_total_incl_tax, base_row_total_incl_tax, hidden_tax_amount, base_hidden_tax_amount, weee_tax_disposition, weee_tax_row_disposition, base_weee_tax_disposition, base_weee_tax_row_disposition, weee_tax_applied_amount, weee_tax_applied_row_amount, base_weee_tax_applied_amount, base_weee_tax_applied_row_amnt, gw_base_price, gw_price, gw_base_tax_amount, gw_tax_amount', 'length', 'max'=>12),
			array('updated_at, description, applied_rule_ids, additional_data, weee_tax_applied', 'safe'),
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Mage1SalesQuoteItem the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
