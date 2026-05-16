#!/usr/bin/env python3
from pathlib import Path
import re
import sys


ROOT = Path(__file__).resolve().parents[1]
INVOICE_VIEW = ROOT / "api" / "modules" / "v2" / "views" / "order" / "invoice.php"


def fail(message):
    print(f"invoice escaping check failed: {message}", file=sys.stderr)
    sys.exit(1)


text = INVOICE_VIEW.read_text(encoding="utf-8")

required_snippets = [
    "use yii\\helpers\\Html;",
    "$encode = static function ($value) {",
    "return Html::encode((string) $value);",
    "rawurlencode((string) $order->restaurant_uuid)",
    "rawurlencode((string) $order->restaurant->logo)",
    "<?= $encode($defaultLogo) ?>",
    "<?= $encode($order->armada_qr_code_link) ?>",
    "<?= $encode($order->restaurant->name) ?>",
    "<?= $encode($order->customer_name) ?>",
    "<?= $encode($orderItem->item_name) ?>",
    "<?= $encode($orderItem->customer_instruction) ?>",
    "<?= $encode($orderItem->getOrderExtraOptionsText()) ?>",
    "Voucher Discount (<?= $encode($order->voucher->code) ?>)",
]

for snippet in required_snippets:
    if snippet not in text:
        fail(f"missing expected escaping guard: {snippet}")

raw_echo_patterns = [
    r'<\?=\s*\$defaultLogo\s*\?>',
    r'src="<\?=\s*\$order->armada_qr_code_link\s*\?>"',
    r'<\?=\s*\$order->restaurant->name\s*\?>',
    r'<\?=\s*\$order->customer_name\s*\?>',
    r'<\?=\s*\$order->customer_phone_number\s*\?>',
    r'<\?=\s*\$order->special_directions\s*\?>',
    r'<\?=\s*\$orderItem->item_name\s*\?>',
    r'<\?=\s*\$orderItem->customer_instruction\s*\?>',
    r'<\?=\s*\$orderItem->getOrderExtraOptionsText\(\)\s*\?>',
    r'Voucher Discount \(<\?=\s*\$order->voucher->code\s*\?>\)',
]

for pattern in raw_echo_patterns:
    if re.search(pattern, text):
        fail(f"found unescaped output matching {pattern}")

print("api v2 invoice escaping check passed")
