<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use common\models\Order;
use common\models\BankDiscount;
use common\models\Voucher;

/* @var $this yii\web\View */
/* @var $model common\models\Order */
/* @var $orderItems any */

$encode = static function ($value) {
    return Html::encode((string) $value);
};

$this->title = 'Invoice #' . $model->order_uuid;

$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index', 'storeUuid' => $model->restaurant_uuid]];
$this->params['breadcrumbs'][] = ['label' => 'Order #' . $model->order_uuid, 'url' => ['view', 'id' => $model->order_uuid, 'storeUuid' => $model->restaurant_uuid]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['restaurant_uuid'] = $model->restaurant_uuid;
?>
<!-- invoice functionality start -->
<section class="invoice-print mb-1">
    <div>
        <button class="btn btn-primary btn-print mb-1 mb-md-0"> <i class="feather icon-file-text"></i> Print</button>
    </div>
</section>
<!-- invoice functionality end -->
<!-- invoice page -->
<section class="card invoice-page">


    <div id="invoice-template" class="card-body">
        <!-- Invoice Company Details -->
        <div id="invoice-company-details" class="row">
            <div class="col-sm-6 col-12 text-left">
                <div class="media " style="margin-bttom: 20px;     display: block;">
                    <?php if ($model->armada_qr_code_link) { ?>
                        <img src="<?= $encode($model->armada_qr_code_link) ?>" width="100" height="100" />
                    <?php } ?>
                    <img src="<?= $encode($model->restaurant->getRestaurantLogoUrl()) ?>" />

                </div>

                <div style="margin-top:30px">
                  <h3 class="invoice-logo"><?= $encode($model->restaurant->name) ?></h3>
                  <!-- <p class="card-text mb-25">Office 149, 450 South Brand Brooklyn</p>
                  <p class="card-text mb-25">San Diego County, CA 91905, USA</p>
                  <p class="card-text mb-0">+1 (123) 456 7891, +44 (876) 543 2198</p> -->

                  <?php
                if($model->order_mode == Order::ORDER_MODE_DELIVERY){
              ?>

                <!-- <p class="card-text mb-25"> -->
                  <?php
                  // echo $model->customer_name
                  ?>
                <!-- </p> -->

                <p class="card-text mb-25"  style="display: contents">
                  <?= $encode($model->area_id && $model->block ? 'Block ' . $model->block : '') ?>
                </p>
                <p class="card-text mb-25"  style="display: contents">
                  <?= $encode($model->area_id ? 'Street ' . $model->street : '') ?>
                </p>

                <?php
              if($model->unit_type == 'Apartment'  ||  $model->unit_type == 'Office'){
            ?>

                <div  style="display: block">
                  <p class="card-text mb-25"  style="display: contents">
                    <?= $encode($model->area_id && $model->avenue ? 'Avenue ' . $model->avenue : '') ?>
                  </p>

                  <p class="card-text mb-25"  style="display: contents">
                    <?= $encode($model->area_id && $model->floor != null ? 'Floor ' . $model->floor : '') ?>
                  </p>
                  <p class="card-text mb-25"  style="display: contents">
                    <?= $encode($model->area_id && $model->apartment != null && $model->unit_type && strtolower($model->unit_type) == Order::UNIT_TYPE_APARTMENT ? 'Apartment No. ' . $model->apartment : '') ?>
                  </p>
                  <p class="card-text mb-25"  style="display: contents">
                    <?= $encode($model->area_id && $model->office != null && $model->unit_type && strtolower($model->unit_type) == Order::UNIT_TYPE_OFFICE ? 'Office No. ' . $model->office : '') ?>
                  </p>
                  <?php if ($model->area_id) { ?>
                    <p class="card-text mb-25"  style="display: block">
                      <?= $model->unit_type == Order::UNIT_TYPE_HOUSE ? 'House No.' : 'Building'  ?>
                      &nbsp;
                      <?= $encode($model->house_number) ?>
                    </p>
                  <?php } ?>

                </div>
                <?php } else { ?>
                  <div  style="display: block">

                    <p class="card-text mb-25"  style="display: contents">
                      <?= $encode($model->area_id && $model->avenue ? 'Avenue ' . $model->avenue : '') ?>
                    </p>
                    <p class="card-text mb-25"  style="display: contents">
                      <?= $encode($model->area_id ? ($model->unit_type && strtolower($model->unit_type) == Order::UNIT_TYPE_HOUSE ? 'House No. ' : 'Building: ') . $model->house_number : '') ?>
                    </p>
                    <p class="card-text mb-25" style="display: block">
                      <?= $encode($model->address_1 ? $model->address_1 : '') ?>
                    </p>
                    <p class="card-text mb-25" style="display: contents">
                      <?= $encode($model->address_2 ? $model->address_2 : '') ?>
                    </p>

                  </div>
              <?php } ?>

                <div  style="display: block">
                  <p class="card-text mb-25" style="display: contents">
                    <?= $encode($model->area_id ? $model->area_name . ', ' : '') ?>
                  </p>
                    <p class="card-text mb-25"  style="display: contents">
                      <?= $encode($model->area_id ? $model->area->city->city_name : $model->city . ' ' . $model->postalcode) ?>
                    </p>
                    <p class="card-text mb-25"  style="display: block">
                       <?= $encode($model->country_name ? $model->country_name : '') ?>
                    </p>
                    <p class="card-text mb-25"  style="display: block">
                      <?= $encode($model->customer_phone_number) ?>
                    </p>
                </div>



                <?php
              } else {
                ?>
                <h6 class="mt-2">Customer</h6>
                  <p class="card-text mb-25">
                    <?= $encode($model->customer_name) ?>
                  </p>
                  <span style="display: block" >
                    <?= $encode($model->customer_phone_number) ?>
                  </span>
              <?php } ?>

                </div>
            </div>
            <div class="col-sm-6 col-12 text-right">
              <h2 class="invoice-title">
                  <b>INVOICE</b>
              </h2>
              <div class="invoice-date-wrapper">
                  <p class="invoice-date-title"><b># INV-<?= $encode($model->order_uuid) ?></b></p>
              </div>

            </div>
        </div>
        <!--/ Invoice Company Details -->

        <!-- Invoice Recipient Details -->
        <div id="invoice-company-details" class="row">
            <div class="col-sm-6 col-12 text-left">



              <?php if($model->special_directions) { ?>


              <div class="invoice-details my-2">
                  <div class="row">

                    <div class=" col-12 text-left">
                      <span>
                        <b>Customer </b>
                        <span style="    padding-left: 10px;">
                          <?= $encode($model->customer_name) ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>
              <div class="invoice-details my-2">
                  <div class="row">

                    <div class=" col-12 text-left">
                      <span>
                        <b>Payment Method</b>
                        <span style="    padding-left: 10px;">
                          <?php if(!empty($model->payment_method_name))
                                    echo $encode($model->payment_method_name);
                                else if(!empty($model->payment_method_name_ar))
                                    echo $encode($model->payment_method_name_ar);
                                else if($model->paymentMethod)
                                    echo $encode($model->paymentMethod->payment_method_name);
                                else
                                    echo "KNET"; ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>
              <div class="invoice-details my-2">
                  <div class="row">

                    <div class="col-12 text-left">
                      <span>
                        <b>Special Directions </b>
                        <span style="    padding-left: 10px;">
                          <?= $encode($model->special_directions) ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>

            <?php  } else { ?>

              <div class="invoice-details my-2">
                  <div class="row">

                    <div class=" col-12 text-left">
                      <span>
                        <span style="    padding-left: 10px;">
                        </span>
                      </span>

                    </div>

                  </div>


              </div>



              <div class="invoice-details my-2">
                  <div class="row">

                    <div class=" col-12 text-left">
                      <span>
                        <b>Customer </b>
                        <span style="    padding-left: 10px;">
                          <?= $encode($model->customer_name) ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>


              <div class="invoice-details my-2">
                  <div class="row">

                    <div class=" col-12 text-left">
                      <span>
                        <b>Payment Method</b>
                        <span style="    padding-left: 10px;">
                          <?= $encode($model->payment_method_name) ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>

            <?php  } ?>


            </div>
            <div class="col-sm-6 col-12 text-right">

                <div class="invoice-details my-2">
                    <div class="row">
                      <div class="col-sm-1 col-12 text-left">
                      </div>
                      <div class="col-sm-5 col-12 text-left">
                        <span><b>Invoice Date</b></span>

                      </div>
                      <div class="col-sm-6 col-12 text-right">
                        <span>
                          <?= \Yii::$app->formatter->asDatetime($model->order_created_at, 'MMM dd, yyyy h:mm a') ?>
                        </span>

                      </div>
                    </div>


                </div>
                <div class="invoice-details my-2">
                    <div class="row">
                      <div class="col-sm-1 col-12 text-left">
                      </div>
                      <div class="col-sm-5 col-12 text-left">
                        <span><b>Estimated Delivery</b></span>

                      </div>
                      <div class="col-sm-6 col-12 text-right">
                        <span>
                          <?= \Yii::$app->formatter->asDatetime($model->estimated_time_of_arrival, 'MMM dd, yyyy h:mm a') ?>
                        </span>

                      </div>
                    </div>


                </div>
                <div class="invoice-details my-2">
                    <div class="row">
                      <div class="col-sm-1 col-12 text-left">
                      </div>
                      <div class="col-sm-5 col-12 text-left">
                        <span><b>When</b></span>

                      </div>
                      <div class="col-sm-6 col-12 text-right">
                        <span>
                          <?=  $model->is_order_scheduled ? 'Scheduled' : 'As soon as possible'; ?>
                        </span>

                      </div>
                    </div>


                </div>

            </div>
        </div>

<?php
if ($model->recipient_name || $model->recipient_phone_number || $model->gift_message || $model->sender_name) {

?>
        <hr />


        <h4>
        <img src="https://res.cloudinary.com/plugn/image/upload/v1649461316/icon_gift_gfapfu.svg" />  Gift details
        </h4>
        <div id="invoice-company-details" class="row">
            <div class="col-sm-6 col-12 text-left">


              <?php if ($model->recipient_name) { ?>

                <div class="invoice-details my-2">
                    <div class="row">

                      <div class=" col-12 text-left">
                        <span>
                          <b>Recipient Name </b>
                          <span style="    padding-left: 10px;">
                            <?= $encode($model->recipient_name) ?>
                          </span>
                        </span>

                      </div>

                    </div>


                </div>

              <?php } ?>


            <?php if ($model->gift_message) { ?>
              <div class="invoice-details my-2">
                  <div class="row">

                    <div class="col-12 text-left">
                      <span>
                        <b>Gift Message </b>
                        <span style="    padding-left: 10px;">
                          <?= $encode($model->gift_message) ?>
                        </span>
                      </span>

                    </div>

                  </div>


              </div>
            <?php } ?>





            </div>
            <?php if ($model->recipient_phone_number) { ?>

                <div class="col-sm-6 col-12 text-right">

                    <div class="invoice-details my-2">
                        <div class="row">
                          <div class="col-sm-1 col-12 text-left">
                          </div>
                          <div class="col-sm-5 col-12 text-left">
                            <span>
                              <b>Recipient Phone Number </b>

                            </span>

                          </div>
                          <div class="col-sm-6 col-12 text-right">
                            <span>
                              <?= $encode($model->recipient_phone_number) ?>

                            </span>

                          </div>
                        </div>


                    </div>



                </div>

            <?php } ?>

        </div>

<?php } ?>



        <!--/ Invoice Recipient Details -->

        <!-- Invoice Items Details -->
        <div id="invoice-items-details" class="pt-1 invoice-items-table">
            <div class="row">
                <div class="table-responsive col-12">

                    <?=
                    GridView::widget([
                        'dataProvider' => $orderItems,
                        'sorter' => false,
                        'columns' => [
                            'item_name',
                            [
                                'label' => 'SKU',
                                'format' => 'text',
                                'value' => 'item.sku',
                            ],
                            [
                                'label' => 'Barcode',
                                'format' => 'text',
                                'value' => 'item.barcode',
                            ],
                            'customer_instruction',
                            'qty',
                            [
                                'label' => 'Extra Options',
                                'value' => function ($data) {
                                    $extraOptions = '';

                                    foreach ($data->orderItemExtraOptions as $key => $extraOption) {

                                        if ($key == 0)
                                            $extraOptions .= $extraOption['extra_option_name'];
                                        else
                                            $extraOptions .= ', ' . $extraOption['extra_option_name'];
                                    }

                                    return $extraOptions;
                                },
                                'format' => 'text'
                            ],
                            [
                                'label' => 'Subtotal',
                                'value' => function ($orderItem) use ($model)  {
                                    return Yii::$app->formatter->asCurrency($orderItem->item_price * $model->currency_rate, $model->currency_code, [
                                        \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                    ]);
                                }
                            ],
                        ],
                        'layout' => '{items}',
                        'tableOptions' => ['class' => 'table  table-hover item'],
                    ]);
                    ?>
                    <hr>
                </div>
            </div>
        </div>
        <div id="invoice-total-details  invoice-sales-total-wrapper" class="invoice-total-table">
          <!-- <div class="row invoice-sales-total-wrapper"> -->
              <div>
            <!-- <div class="row"> -->
                <!-- <div class="col-12"> -->
                    <div class="table-responsive">
                        <table class="table summary" style="  width: 30%; float: right;">
                            <tbody>
                                <tr>
                                    <th>Subtotal</th>
                                    <td><?= Yii::$app->formatter->asCurrency($model->subtotal * $model->currency_rate, $model->currency_code, [
                                            \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                        ]); ?></td>
                                </tr>
                                <?php
                                if ($model->voucher_id != null && $model->voucher_id && $model->voucher->discount_type !== Voucher::DISCOUNT_TYPE_FREE_DELIVERY) {
                                    $voucherDiscount = $model->voucher->discount_type == Voucher::DISCOUNT_TYPE_PERCENTAGE ? ($model->subtotal * ($model->voucher->discount_amount / 100)) : $model->voucher->discount_amount;
                                    $subtotalAfterDiscount = $model->subtotal - $voucherDiscount;
                                    ?>
                                    <tr>
                                        <th>Voucher Discount</th>
                                        <td><?= Yii::$app->formatter->asCurrency($voucherDiscount * $model->currency_rate, $model->currency_code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ]) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Subtotal After Voucher</th>
                                        <td>
                                          <?php
                                            $subtotalAfterDiscount = $subtotalAfterDiscount > 0 ? $subtotalAfterDiscount : 0;

                                            echo Yii::$app->formatter->asCurrency($subtotalAfterDiscount * $model->currency_rate, $model->currency_code, [
                                                    \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                    \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ])
                                          ?>
                                        </td>
                                    </tr>
                                    <?php
                                } else if ($model->bank_discount_id != null && $model->bank_discount_id) {
                                    $bankDiscount = $model->bankDiscount->discount_type == BankDiscount::DISCOUNT_TYPE_PERCENTAGE ? ($model->subtotal * ($model->bankDiscount->discount_amount / 100)) : $model->bankDiscount->discount_amount;
                                    $subtotalAfterDiscount = $model->subtotal - $bankDiscount;

                                    $subtotalAfterDiscount = $subtotalAfterDiscount > 0 ? $subtotalAfterDiscount : 0;


                                    ?>
                                    <tr>
                                        <th>Bank Discount</th>
                                        <td>-<?= Yii::$app->formatter->asCurrency($bankDiscount* $model->currency_rate, $model->currency_code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ]) ?></td>
                                    </tr>
                                <tbody>
                                    <tr>
                                        <th>Subtotal After Bank Discount</th>
                                        <td><?= Yii::$app->formatter->asCurrency($subtotalAfterDiscount* $model->currency_rate, $model->currency_code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ]) ?></td>
                                    </tr>
                                </tbody>
                            <?php } ?>

                            <?php if ($model->order_mode == Order::ORDER_MODE_DELIVERY) { ?>

                                <tr>
                                    <th>Delivery fee</th>
                                    <td><?= \Yii::$app->formatter->asCurrency($model->delivery_fee* $model->currency_rate, $model->currency_code, [
                                            \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                        ]) ?></td>
                                </tr>

                                <?php if ($model->voucher_id != null && $model->voucher_id && $model->voucher->discount_type == Voucher::DISCOUNT_TYPE_FREE_DELIVERY) { ?>
                                    <tr>
                                        <th>Voucher Discount</th>
                                        <td>-<?= Yii::$app->formatter->asCurrency($model->delivery_fee* $model->currency_rate, $model->currency_code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ]) ?></td>

                                    </tr>

                                    <tr>
                                        <th>Delivery fee After Voucher</th>
                                        <td><?= Yii::$app->formatter->asCurrency(0, $model->currency_code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                                \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                            ]) ?></td>
                                    </tr>
                                <?php } ?>

                            <?php } ?>
                            <?php if ($model->tax > 0) { ?>
                            <tr>
                                <th>Tax</th>
                                <td><?= Yii::$app->formatter->asCurrency($model->tax* $model->currency_rate, $model->currency_code, [
                                        \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                        \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                    ]) ?></td>
                            </tr>
                          <?php } ?>
                            <tr>
                                <th><b>Total Price</b></th>
                                <td><?= Yii::$app->formatter->asCurrency($model->total, $model->currency_code, [
                                        \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                        \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                    ]) ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- invoice page end -->
