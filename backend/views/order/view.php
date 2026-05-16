
<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use common\models\Order;
use common\models\Voucher;
use common\models\BankDiscount;

/* @var $this yii\web\View */
/* @var $model common\models\Order */

$encode = static function ($value) {
    return Html::encode((string) $value);
};

$this->title = 'Order #' . $model->order_uuid;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);




if ($model->recipient_name || $model->recipient_phone_number || $model->gift_message || $model->sender_name) {
  $this->registerCss('
  .content-header-title::after {
    content: url(https://res.cloudinary.com/plugn/image/upload/v1649461316/icon_gift_gfapfu.svg);
        vertical-align: middle;
        padding:10px;
  }
  ');
}


$js = "
$(function () {
  $('.summary').insertAfter('.top');
  $('.top').css('display', 'none');
});

";
$this->registerJs($js);
?>


<?php if (Yii::$app->session->getFlash('errorResponse') != null) { ?>

    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fa fa-ban"></i> Error!</h5>
        <?= $encode(Yii::$app->session->getFlash('errorResponse')) ?>
    </div>
<?php } elseif (Yii::$app->session->getFlash('successResponse') != null) { ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fa fa-check"></i> Success!</h5>
        <?= $encode(Yii::$app->session->getFlash('successResponse')) ?>
    </div>
<?php }


$armadaApiKey = null;
$mashkorBranchId = null;

if  (
    $model->delivery_zone_id &&
    $model->deliveryZone->business_location_id &&
    $model->deliveryZone->businessLocation
) {
    if ($model->deliveryZone->businessLocation->armada_api_key != null) {
        $armadaApiKey = $model->deliveryZone->businessLocation->armada_api_key;
    }

    if ($model->deliveryZone->businessLocation->mashkor_branch_id != null) {
        $mashkorBranchId = $model->deliveryZone->businessLocation->mashkor_branch_id;
    }

    if ($model->deliveryZone->businessLocation->diggipack_customer_id != null) {
        $diggipack_customer_id = $model->deliveryZone->businessLocation->diggipack_customer_id;
    }
}
?>

<div class="page-title">
    <h1>
        <?= Html::encode($this->title) ?>
    </h1>
    <p>
        <?php
        if ($model->order_status != Order::STATUS_ABANDONED_CHECKOUT && $model->order_status != Order::STATUS_DRAFT) {
            echo Html::a('<i class="feather icon-file-text"></i> View Invoice', ['view-invoice', 'order_uuid' => $model->order_uuid, 'storeUuid' => $model->restaurant_uuid
                ], ['class' => 'btn btn-outline-primary mr-1 mb-1', 'style' => 'margin-right: 7px']);
        }
        ?>

        <?php
        if ($model->latitude && $model->longitude) {
            echo Html::a('Get directions', 'https://www.google.com/maps/search/?api=1&query=' . $model->latitude . ',' . $model->longitude, ['class' => 'btn btn-warning mr-1 mb-1', 'style' => 'margin-right: 7px;']);
        }
        ?>

    <div style="display: block">

        <?php

        $difference = 0;

        if ($model->estimated_time_of_arrival) {
            $deliveryTime = strtotime($model->estimated_time_of_arrival);
            $currentTime = strtotime('now');
            $difference = round(abs($deliveryTime - $currentTime) / 3600, 2);
        }

        if ($model->order_mode == Order::ORDER_MODE_DELIVERY) {

            if ( ( ($model->area_id && ($model->area->country->country_name == 'Kuwait' || $model->area->country->country_name == 'Bahrain')) || ($model->shipping_country_id && ($model->country->country_name == 'Kuwait' || $model->country->country_name == 'Bahrain'))    )  &&
             $armadaApiKey != null && $model->armada_tracking_link == null
           ) {

                if ( $difference <= 1  && $model->restaurant->hide_request_driver_button && $model->area_id != 4007 ){
                          echo Html::a('Request a driver from Armada', ['request-driver-from-armada', 'order_uuid' => $model->order_uuid, 'armadaApiKey' => $armadaApiKey], [
                              'class' => 'btn btn-dark mr-1 mb-1',
                              'style' => 'margin-right: 7px;',
                              'data' => [
                                  'confirm' => 'Are you sure you want to request a driver from Armada?',
                                  'method' => 'post',
                              ],
                          ]);
                }

                if ( !$model->restaurant->hide_request_driver_button && $model->area_id != 4007 )  {
                      echo Html::a('Request a driver from Armada', ['request-driver-from-armada', 'order_uuid' => $model->order_uuid , 'armadaApiKey' => $armadaApiKey], [
                          'class' => 'btn btn-dark mr-1 mb-1',
                          'style' => 'margin-right: 7px;',
                          'data' => [
                              'confirm' => 'Are you sure you want to request a driver from Armada?',
                              'method' => 'post',
                          ],
                      ]);
                    }

            }

            if (( ($model->area_id && $model->area->country->country_name == 'Kuwait') || ($model->shipping_country_id && $model->country->country_name == 'Kuwait' )    ) &&
              $mashkorBranchId != null && $model->mashkor_order_number == null) {

                if ($difference <= 1  && $model->restaurant->hide_request_driver_button ){
                  echo Html::a('Request a driver from Mashkor', ['request-driver-from-mashkor',  'order_uuid' => $model->order_uuid, 'mashkorBranchId' => $mashkorBranchId], [
                      'class' => 'btn btn-dark mr-1 mb-1',
                      'style' => 'margin-right: 7px;',
                      'data' => [
                          'confirm' => 'Are you sure you want to request a driver from Mashkor?',
                          'method' => 'post',
                      ],
                  ]);
                }


                if (!$model->restaurant->hide_request_driver_button){
                          echo Html::a('Request a driver from Mashkor', ['request-driver-from-mashkor',  'order_uuid' => $model->order_uuid, 'mashkorBranchId' => $mashkorBranchId], [
                              'class' => 'btn btn-dark mr-1 mb-1',
                              'style' => 'margin-right: 7px;',
                              'data' => [
                                  'confirm' => 'Are you sure you want to request a driver from Mashkor?',
                                  'method' => 'post',
                              ],
                          ]);
                        }
            }
        }
        ?>

    </div>




</p>



</div>

<div class="order-view">

    <div class="card">

        <div class="card-body">

            <div class="box-body table-responsive no-padding">

                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'order_status',
                            'format' => 'html',
                            'value' => function ($data) {
                                return Html::tag('span', Html::encode($data->orderStatusInEnglish), ['style' => 'font-size:25px; font-weight: 700']);
                            },
                        ],
                        [
                            'label' => 'Store',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a(
                                        $data->restaurant->name,
                                        \yii\helpers\Url::to(['restaurant/view', 'id' => $data->restaurant_uuid]), ['target' => '_blank']);
                            },
                        ],
                        [
                            'attribute' => 'business_location_name',
                            "format" => "text",
                            "value" => function($model) {
                               return $model->business_location_name ? $model->business_location_name : '';
                            },
                            'visible' => $model->business_location_name != null
                        ],
                        [
                            'attribute' => 'order_mode',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->order_mode == Order::ORDER_MODE_DELIVERY ? 'Delivery' : 'Pickup';
                            },
                        ],
                        'is_market_order:boolean',
                        // 'total_price:currency',
                        // 'subtotal:currency',
                        // 'delivery_fee:currency',
                        [
                            'attribute' => 'order_created_at',
                            "format" => "raw",
                            "value" => function($model) {
                                return date('l d M, Y - h:i A', strtotime($model->order_created_at));
                            }
                        ],
                        [
                            'label' => 'When',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->is_order_scheduled ? 'Scheduled' : 'As soon as possible';
                            },
                        ],
                        [
                            'attribute' => 'estimated_time_of_arrival',
                            'label' => 'Estimated Delivery',
                            "format" => "raw",
                            "value" => function($model) {
                                if (!$model->estimated_time_of_arrival) {
                                    return null;
                                }

                                if($model->is_order_scheduled)
                                  return date('l d M, Y - h:i A', strtotime($model->estimated_time_of_arrival)) .  date(' - h:i A', strtotime($model->scheduled_time_to));
                                else
                                  return date('l d M, Y - h:i A', strtotime($model->estimated_time_of_arrival));
                            }
                        ],
                        // [
                        //     'attribute' => 'diggipack_awb_no',
                        //     'format' => 'raw',
                        //     'value' => function ($data) {
                        //         return $data->diggipack_awb_no;
                        //     },
                        //     'visible' => $model->diggipack_awb_no != null,
                        // ],
                        // [
                        //     'label' => 'DiggiPacks tracking link',
                        //     'format' => 'raw',
                        //     'value' => function ($data) {
                        //         return Html::a('https://track.diggipacks.com/result_detailfm/' . $data->diggipack_awb_no, \yii\helpers\Url::to('https://track.diggipacks.com/result_detailfm/' . $data->diggipack_awb_no, true), ['target' => '_blank']);
                        //     },
                        //     'visible' => $model->diggipack_awb_no != null,
                        // ],
                        [
                            'attribute' => 'armada_tracking_link',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->armada_tracking_link, \yii\helpers\Url::to($data->armada_tracking_link, true), ['target' => '_blank']);
                            },
                            'visible' => $model->armada_tracking_link != null,
                        ],
                        [
                            'attribute' => 'armada_delivery_code',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->armada_delivery_code, \yii\helpers\Url::to($data->armada_delivery_code, true), ['target' => '_blank']);
                            },
                            'visible' => $model->armada_delivery_code != null,
                        ],
                        [
                            'attribute' => 'armada_order_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->armada_order_status ? Html::tag('span', Html::encode($data->armada_order_status), ['style' => 'font-size:20px; font-weight: 700']) : null;
                            },
                            'visible' => $model->armada_order_status != null,
                        ],
                        'aramex_shipment_id',
                        'aramex_pickup_id',
                        [
                            'attribute' => 'mashkor_order_number',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->mashkor_order_number ? Html::encode($data->mashkor_order_number) : null;
                            },
                            'visible' => $model->mashkor_order_number != null,
                        ],
                        [
                            'attribute' => 'mashkor_order_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->mashkor_order_status ? Html::tag('span', Html::encode(Yii::$app->mashkorDelivery->getOrderStatus($data->mashkor_order_status)), ['style' => 'font-size:20px; font-weight: 700']) : null;
                            },
                            'visible' => $model->mashkor_order_status != null,
                        ],
                        [
                            'attribute' => 'mashkor_tracking_link',
                            'format' => 'raw',
                            'value' => function ($data) {


                                return Html::a($data->mashkor_tracking_link, \yii\helpers\Url::to($data->mashkor_tracking_link, true), ['target' => '_blank']);
                            },
                            'visible' => $model->mashkor_tracking_link != null,
                        ],
                        [
                            'attribute' => 'mashkor_driver_phone',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->mashkor_driver_phone ? Html::encode($data->mashkor_driver_phone) : null;
                            },
                            'visible' => $model->mashkor_driver_phone != null,
                        ],
                        [
                            'attribute' => 'mashkor_driver_name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data->mashkor_driver_name ? Html::encode($data->mashkor_driver_name) : null;
                            },
                            'visible' => $model->mashkor_driver_name != null,
                        ],
                    ],
                    'options' => ['class' => 'table table-hover text-nowrap table-bordered'],
                ])
                ?>

            </div>
        </div>
    </div>
    <?php if ($orderItems->totalCount) { ?>
        <section id="data-list-view" class="data-list-view-header">

            <div class="card">
                <div class="card-body">
                    <div class="box-body table-responsive no-padding">

                        <?=
                        GridView::widget([
                            'dataProvider' => $orderItems,
                            'sorter' => false,
                            'columns' => [
                                [
                                    'label' => 'Item image',
                                    'format' => 'html',
                                    'value' => function ($data) {
                                        $itemItmage = $data->itemImage;

                                        if ($data->itemImage && $data->itemImage->product_file_name) {
                                            return Html::img("https://res.cloudinary.com/plugn/image/upload/c_scale,h_105,w_105/restaurants/" . $data->restaurant_uuid. "/items/" . $itemItmage->product_file_name);
                                        }
                                    },
                                    'contentOptions' => ['style' => 'width: 100px;'],
                                ],
                                'item_name',
                                [
                                    'label' => 'SKU',
                                    'format' => 'text',
                                    'value' => function ($data) {

                                        if($data->variant) {
                                            return $data->variant->sku;
                                        }else if($data->item) {
                                            return $data->item->sku;
                                        }
                                    },
                                ],
                                [
                                    'label' => 'Barcode',
                                    'format' => 'text',
                                    //'value' => 'item.barcode',
                                    'value' => function ($data) {

                                        if($data->variant) {
                                            return $data->variant->barcode;
                                        }else if($data->item) {
                                            return $data->item->barcode;
                                        }
                                    },
                                ],
                                // 'customer_instruction',
                                [
                                    'attribute' => 'customer_instruction',
                                    'format' => 'html',
                                    'value' => function ($data) {
                                        return $data->customer_instruction ? Html::tag('b', Html::encode($data->customer_instruction)) : '(not set)' ;
                                    }
                                ],
                                [
                                    'attribute' => 'qty',
                                    'format' => 'html',
                                    'value' => function ($data) {
                                        return  '<b>' . $data->qty  . '</b>';
                                    }
                                ],
                                [
                                    'label' => 'Extra Options',
                                    'value' => function ($data) {
                                        $extraOptions = '';

                                        foreach ($data->orderItemExtraOptions as $key => $extraOption) {
                                            if ($key == 0) {
                                                $extraOptions .= $extraOption['extra_option_name'];
                                            } else {
                                                $extraOptions .= ', ' . $extraOption['extra_option_name'];
                                            }
                                        }

                                        return $extraOptions;
                                    },
                                    'format' => 'text'
                                ],
                                [
                                    'label' => 'Subtotal',
                                    'value' => function ($orderItem) use ($model)  {
                                        return Yii::$app->formatter->asCurrency($orderItem->item_price * $model->currency_rate, $orderItem->currency->code, [
                                            \NumberFormatter::MAX_FRACTION_DIGITS => $orderItem->currency->decimal_place
                                        ]);
                                    }
                                ],
                            ],
                            'layout' => '{items}{pager}',
                            'tableOptions' => ['class' => 'table table-bordered table-hover'],
                        ]);
                        ?>

                    </div>
                </div>
            </div>
        </section>
    <?php } ?>

    <?php

    $totalNumberOfItems = $orderItems->query->count();

    if ( $totalNumberOfItems > 0 ) {
                        ?>
        <div class="card">
            <div class="card-body">
                <h3>
        <?php
        if ($model->order_status == Order::STATUS_PARTIALLY_REFUNDED) {
            echo 'Partially refunded';
        } elseif ($model->order_status == Order::STATUS_REFUNDED) {
            echo 'Refunded';
        } else if ($model->order_status != Order::STATUS_REFUNDED && $model->order_status != Order::STATUS_PARTIALLY_REFUNDED && $model->payment_method_id != 3 && $model->payment_uuid && $model->payment->payment_current_status == 'CAPTURED') {
            echo 'Paid';
        } else {
            echo 'Payment pending';
        }
        ?>
                </h3>


                <table class="order-details-summary-table"  style="width: 100%; border-collapse: separate; border-spacing: 0; padding: 0.4rem 0.8rem; border: 0; vertical-align: top;">
                    <tbody>
                        <tr>
                            <td colspan="2">Subtotal</td>
                            <td style="float: right;"><?= Yii::$app->formatter->asCurrency($model->subtotal* $model->currency_rate, $model->currency->code, [
                                    \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                    \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                ]) ?></td>
                        </tr>
                    </tbody>
                    <?php
                    if ($model->voucher_id != null && $model->voucher_id && $model->voucher->discount_type !== Voucher::DISCOUNT_TYPE_FREE_DELIVERY) {
                        $voucherDiscount = $model->voucher->discount_type == Voucher::DISCOUNT_TYPE_PERCENTAGE ? ($model->subtotal * ($model->voucher->discount_amount / 100)) : $model->voucher->discount_amount;
                        $subtotalAfterDiscount = $model->subtotal - $voucherDiscount;
                        ?>
                        <tbody>
                            <tr>
                                <td colspan="2">Voucher Discount (<?= $encode($model->voucher->code) ?>)</td>
                                <td style="float: right;">-<?= Yii::$app->formatter->asCurrency(
                                        $voucherDiscount* $model->currency_rate, $model->currency->code, [
                                                \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                            \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                        ]) ?></td>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td colspan="2">Subtotal After Voucher</td>
                                <td style="float: right;">
                                  <?php

                                  $subtotalAfterDiscount = $subtotalAfterDiscount > 0 ?  $subtotalAfterDiscount : 0;

                                  echo Yii::$app->formatter->asCurrency($subtotalAfterDiscount* $model->currency_rate, $model->currency->code, [
                                          \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                     \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place
                                  ])

                                  ?>
                                </td>
                            </tr>
                        </tbody>
                        <?php
                    } else if ($model->bank_discount_id != null && $model->bank_discount_id) {
                                $bankDiscount = $model->bankDiscount->discount_type == BankDiscount::DISCOUNT_TYPE_PERCENTAGE ? ($model->subtotal * ($model->bankDiscount->discount_amount / 100)) : $model->bankDiscount->discount_amount;
                                $subtotalAfterDiscount = $model->subtotal - $bankDiscount;
                                ?>
                                <tbody>
                                    <tr>
                                        <td colspan="2">Bank Discount</td>
                                        <td style="float: right;">-<?= Yii::$app->formatter->asCurrency($bankDiscount* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr>
                                        <td colspan="2">Subtotal After Bank Discount</td>
                                        <td style="float: right;">
                                          <?php

                                            $subtotalAfterDiscount = $subtotalAfterDiscount > 0 ? $subtotalAfterDiscount : 0;

                                            echo Yii::$app->formatter->asCurrency($subtotalAfterDiscount* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place])
                                          ?>
                                        </td>
                                    </tr>
                                </tbody>
                            <?php }
                            ?>

                    <tbody>
                        <tr>
                            <td colspan="2">Delivery fee</td>
                            <td style="float: right;"><?= Yii::$app->formatter->asCurrency($model->delivery_fee* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                        </tr>
                    </tbody>

                    <?php if ($model->voucher_id != null && $model->voucher_id && $model->voucher->discount_type == Voucher::DISCOUNT_TYPE_FREE_DELIVERY) {
                        ?>
                        <tbody>
                            <tr>
                                <td colspan="2">Voucher Discount (<?= $encode($model->voucher->code) ?>)</td>
                                <td style="float: right;">-<?= Yii::$app->formatter->asCurrency($model->delivery_fee* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                            </tr>
                        </tbody>

                        <tbody>
                            <tr>
                                <td colspan="2">Delivery fee After Voucher</td>
                                <td style="float: right;"><?= Yii::$app->formatter->asCurrency(0, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                            </tr>
                        </tbody>
    <?php } ?>


                  <?php if ($model->tax > 0) { ?>
                    <tbody>
                        <tr>
                            <td colspan="2">Tax</td>
                            <td style="float: right;"><?= Yii::$app->formatter->asCurrency($model->tax* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                        </tr>
                    </tbody>
                    <?php } ?>


                    <tbody>
                        <tr>
                            <td colspan="2">Total</td>
                            <td style="float: right;"><?= Yii::$app->formatter->asCurrency($model->total, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                        </tr>
                    </tbody>

                    <tbody>
                        <tr>
                            <td colspan="3" class="order-details-summary-table__separator"><hr /></td>
                        </tr>
                    </tbody>
    <?php
    if ($model->order_status == Order::STATUS_REFUNDED || $model->order_status == Order::STATUS_PARTIALLY_REFUNDED && ($model->refunds > 0)) {
        foreach ($model->refunds as $refund) {
            ?>
                            <tbody class="order-details__summary__refund-lines">
                                <tr class="order-details__summary__detail-line-row">
                                    <td>Refunded</td>
                                    <td class="type--subdued">
                                        Reason:  <?= $refund->reason ? $encode($refund->reason) : ' –' ?>
                                    </td>
                                    <td style="float: right;">-<?= Yii::$app->formatter->asCurrency($refund->refund_amount* $model->currency_rate, $model->currency->code, [\NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place, \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place]) ?></td>
                                </tr>
                            </tbody>

                            <?php
                        }
                    }
                    ?>
                    <tbody class="order-details__summary__net-payment">
                        <tr>
                            <td class="type--bold" colspan="2">Net payment</td>
                            <td style="float: right;"><?= Yii::$app->formatter->asCurrency($model->total, $model->currency->code, [
                                    \NumberFormatter::MIN_FRACTION_DIGITS => $model->currency->decimal_place,
                                    \NumberFormatter::MAX_FRACTION_DIGITS => $model->currency->decimal_place])
                                ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
                <?php } ?>

<?php


if ($refundDataProvider->totalCount > 0 && $model->payment) {
    ?>
        <div class="card">
            <div class="card-body">

                <h3 style="margin-bottom: 20px;">
                <?php
                  if($refundDataProvider->totalCount == 1)
                    echo 'Refund';
                  else if($refundDataProvider->totalCount > 1)
                    echo 'Refunds';
                ?>
                </h3>

                <?= GridView::widget([
                    'dataProvider' => $refundDataProvider,
                    'columns' => [
                      ['class' => 'yii\grid\SerialColumn'],

                      [
                          'attribute' => 'refund_amount',
                          "value" => function($data) use ($model) {
                                  return Yii::$app->formatter->asCurrency($data->refund_amount* $model->currency_rate, $data->currency->code, [
                                      \NumberFormatter::MAX_FRACTION_DIGITS => $data->currency->decimal_place
                                  ]);
                          },
                      ],
                        'refund_status',
                    ],
                    'layout' => '{items}{pager} ',
                    'tableOptions' => ['class' => 'table table-bordered table-hover'],


                ]); ?>
            </div>
        </div>

            <?php } ?>

    <div class="card">
        <div class="card-body">
            <h3>
              Payment details
            </h3>

            <?php

            if($model->restaurant->is_tap_enable && $model->payment_uuid && $model->payment->payment_current_status  != 'CAPTURED')
              echo Html::a('Request Payment Status Update from TAP', ['update-payment-status','id' => $model->payment_uuid],
                  ['class'=>'btn btn-outline-primary']);

            // if($model->restaurant->is_myfatoorah_enable && $model->payment_uuid && $model->payment->payment_current_status  != 'Succss')
            //   echo Html::a('Request Payment Status Update from MyFatoorah', ['update-payment-status','id' => $model->payment_uuid],
            //  ['class'=>'btn btn-outline-primary']);

            ?>

            <p>
            <?php
//              if($model->payment_method_id != 3 && $model->order_status != Order::STATUS_REFUNDED) echo Html::a('Create Refund', ['refund/create',  'orderUuid' => $model->order_uuid], ['class' => 'btn btn-success']) ;
            ?>
            <div class="box-body table-responsive no-padding">

                </p>


<?=
DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'label' => 'Payment type',
            'format' => 'html',
            'value' => function ($data) {
                 return $data->getPaymentMethodName();
            },
        ],
        [
            'label' => 'Payment status',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment) {
                    return $data->payment->payment_current_status == 'CAPTURED' || $data->payment->payment_current_status == 'SUCCESS' || $data->payment->payment_current_status == 'Paid'  || $data->payment->payment_current_status == 'Succss' ? '<span class="badge bg-success" style="font-size:20px;" >' . $data->payment->payment_current_status . '</span>' : '<span class="badge bg-danger" style="font-size:20px;" >' . $data->payment->payment_current_status . '</span>';
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment_uuid,
        ],
        [
            'label' => 'Gateway ID',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment_uuid) {
                    return $data->payment->payment_gateway_order_id;
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment_uuid && $model->payment->payment_gateway_name == 'tap',
        ],
        [
            'label' => 'Invoice ID',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment_uuid) {
                    return $data->payment->payment_gateway_invoice_id;
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment_uuid && $model->payment->payment_gateway_name == 'myfatoorah',
        ],
        [
            'label' => 'Received Callback',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment) {
                    return $data->payment->received_callback == true ? 'True' : 'False';
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment
        ],
        [
            'label' => 'Transaction ID',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment) {
                    return $data->payment->payment_gateway_transaction_id;
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment_uuid && $model->payment->payment_gateway_name == 'tap',
        ],
        [
            'label' => 'Payment ID',
            'format' => 'html',
            'value' => function ($data) {
                if ($data->payment) {
                    return $data->payment->payment_gateway_payment_id;
                }
            },
            'visible' => $model->payment_method_id != 3 && $model->payment_uuid && $model->payment->payment_gateway_name == 'myfatoorah' && $model->payment->payment_gateway_payment_id,
        ],
    ],
    'options' => ['class' => 'table table-hover text-nowrap table-bordered'],
])
?>


                <?php if($model->payment && sizeof($model->payment->paymentFails) > 0) { ?>
                    <h4>Payment failed response</h4>
                <?php } ?>

                <?php
                if ($model->payment) {
                    foreach($model->payment->paymentFails as $paymentFail) { ?>
                       <pre> <?php print_r($paymentFail->response) ?></pre>
                    <?php }
                      // unserialize(  . '')
                }
                ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3>Customer Info</h3>

            <div class="box-body table-responsive no-padding">

                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'customer_name',
                        [
                            'attribute' => 'customer_phone_number',
                            "format" => "raw",
                            "value" => function($model) {
                              return '<a href="tel:'. $model->customer_phone_number .'"> '. str_replace(' ', '', $model->customer_phone_number) .' </a>';
                            }
                        ],
                        [
                            'attribute' => 'customer_email',
                            "format" => "raw",
                            "value" => function($model) {
                                return $model->customer_email;
                            },
                            'visible' => $model->customer_email != null && $model->customer_email,
                        ],
                        [
                            'attribute' => 'address_1',
                            'format' => 'html',
                            'value' => function ($data) {
                              return $data->address_1;
                            },
                            'visible' => $model->shipping_country_id ? true : false,
                        ],
                        [
                            'attribute' => 'address_2',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->address_2;
                            },
                            'visible' => $model->shipping_country_id ? true : false,
                        ],
                        [
                            'attribute' => 'postalcode',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->postalcode;
                            },
                            'visible' => $model->shipping_country_id ? true : false,
                        ],
                        [
                            'label' => 'Area',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->area_name;
                            },
                            'visible' => $model->area_id ? true : false,
                        ],
                        [
                            'label' => 'Block',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->block;
                            },
                            'visible' => $model->area_id ? true : false,
                        ],
                        [
                            'label' => 'Street',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->street;
                            },
                            'visible' => $model->area_id ? true : false,
                        ],
                        [
                            'label' => 'Avenue',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->avenue;
                            },
                            'visible' => $model->area_id && $model->avenue ? true : false,
                        ],
                        [
                            'label' => 'Floor',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->floor;
                            },
                            'visible' => $model->area_id && $model->floor != null ? true : false,
                        ],
                        [
                            'label' => 'Office No.',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->office;
                            },
                            'visible' => $model->area_id && $model->unit_type && strtolower($model->unit_type) == Order::UNIT_TYPE_OFFICE && $model->office != null ? true : false,
                        ],
                        [
                            'label' => 'Apartment No.',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->apartment;
                            },
                            'visible' => $model->area_id && $model->unit_type &&  strtolower($model->unit_type) == Order::UNIT_TYPE_APARTMENT && $model->apartment != null ? true : false,
                        ],
                        [
                            'label' => $model->unit_type && strtolower($model->unit_type) == Order::UNIT_TYPE_HOUSE ? 'House No.' : 'Building',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->house_number;
                            },
                            'visible' => $model->area_id  && $model->house_number ? true : false,
                        ],
                        [
                            'label' => 'City',
                            'value' => function ($data) {
                                return  $data->area_id ? $data->area->city->city_name : $data->city;
                            },
                            'visible' => $model->area_id || $model->city
                        ],
                        [
                            'label' => 'Country',
                            'format' => 'html',
                            'value' => function ($data) {
                                return  $data->country_name ? $data->country_name  : '' ;
                            },
                            'visible' => $model->country_name != null
                        ],
                        [
                            'attribute' => 'special_directions',
                            'format' => 'html',
                            'value' => function ($data) {
                                return $data->special_directions;
                            },
                            'visible' => $model->special_directions && $model->special_directions != null,
                        ]
                    ],
                    'options' => ['class' => 'table table-hover text-nowrap table-bordered'],
                ])
                ?>
            </div>
        </div>
    </div>



    <?php
         if ($model->recipient_name || $model->recipient_phone_number || $model->gift_message || $model->sender_name) {
       ?>
           <div class="card">
               <div class="card-body">
                   <h3>Gift details</h3>

                   <div class="box-body table-responsive no-padding">

                       <?=
                       DetailView::widget([
                           'model' => $model,
                           'attributes' => [

                               [
                                   'attribute' => 'sender_name',
                                   "format" => "raw",
                                   "value" => function($model) {
                                       return $model->sender_name;
                                   },
                                   'visible' => $model->sender_name != null && $model->sender_name,
                               ],
                               [
                                   'attribute' => 'recipient_name',
                                   "format" => "raw",
                                   "value" => function($model) {
                                       return $model->recipient_name;
                                   },
                                   'visible' => $model->recipient_name != null && $model->recipient_name,
                               ],
                               [
                                   'attribute' => 'recipient_phone_number',
                                   "format" => "raw",
                                   "value" => function($model) {
                                       return $model->recipient_phone_number;
                                   },
                                   'visible' => $model->recipient_phone_number != null && $model->recipient_phone_number,
                               ],
                               [
                                   'attribute' => 'gift_message',
                                   "format" => "raw",
                                   "value" => function($model) {
                                     return '<span style="    white-space: normal;">' .$model->gift_message . '</span>';
                                   },
                                   'visible' => $model->gift_message != null && $model->gift_message,
                               ],

                           ],
                           'options' => ['class' => 'table table-hover text-nowrap table-bordered'],
                       ])
                       ?>
                   </div>
               </div>
           </div>
         <?php } ?>





</div>

</div>
