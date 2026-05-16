#!/usr/bin/env python3
"""Static guard for backend order detail escaping."""

from pathlib import Path
import re
import sys


ROOT = Path(__file__).resolve().parents[1]
ORDER_VIEW = ROOT / "backend" / "views" / "order" / "view.php"


def fail(message):
    """Print a failing check message and exit with a non-zero status."""
    print(f"backend order detail escaping check failed: {message}", file=sys.stderr)
    sys.exit(1)


text = ORDER_VIEW.read_text(encoding="utf-8")

required_snippets = [
    "$encode = static function ($value) {",
    "return Html::encode((string) $value);",
    "$encode(Yii::$app->session->getFlash('errorResponse'))",
    "$encode(Yii::$app->session->getFlash('successResponse'))",
    "Html::tag('span', Html::encode($data->orderStatusInEnglish)",
    '"format" => "text"',
    "Html::tag('span', Html::encode($data->armada_order_status)",
    "Html::encode($data->mashkor_order_number)",
    "Html::tag('span', Html::encode(Yii::$app->mashkorDelivery->getOrderStatus($data->mashkor_order_status))",
    "Html::encode($data->mashkor_driver_phone)",
    "Html::encode($data->mashkor_driver_name)",
    "Html::tag('b', Html::encode($data->customer_instruction))",
    "Voucher Discount (<?= $encode($model->voucher->code) ?>)",
    "Reason:  <?= $refund->reason ? $encode($refund->reason) : ' –' ?>",
]

for snippet in required_snippets:
    if snippet not in text:
        fail(f"missing expected escaping guard: {snippet}")

raw_patterns = [
    r"<\?=\s*\(Yii::\$app->session->getFlash\('errorResponse'\)\)\s*\?>",
    r"<\?=\s*\(Yii::\$app->session->getFlash\('successResponse'\)\)\s*\?>",
    r"\.\s*\$data->orderStatusInEnglish\s*\.\s*'</span>'",
    r"\.\s*\$data->armada_order_status\s*\.\s*'</span>'",
    r"return\s+\$data->mashkor_order_number\s*\?\s*\$data->mashkor_order_number",
    r"return\s+\$data->mashkor_driver_phone\s*\?\s*\$data->mashkor_driver_phone",
    r"return\s+\$data->mashkor_driver_name\s*\?\s*\$data->mashkor_driver_name",
    r"'<b>'\s*\.\s*\$data->customer_instruction\s*\.\s*'</b>'",
    r"Voucher Discount \(<\?=\s*\$model->voucher->code\s*\?>\)",
    r"Reason:\s*<\?=\s*\$refund->reason\s*\?\s*\$refund->reason",
]

for pattern in raw_patterns:
    if re.search(pattern, text):
        fail(f"found unescaped backend order detail output matching {pattern}")

print("backend order detail escaping check passed")
