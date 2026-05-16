#!/usr/bin/env python3
from pathlib import Path
import re
import sys


ROOT = Path(__file__).resolve().parents[1]
INVOICE_VIEW = ROOT / "backend" / "views" / "order" / "invoice.php"


def fail(message):
    print(f"backend invoice escaping check failed: {message}", file=sys.stderr)
    sys.exit(1)


text = INVOICE_VIEW.read_text(encoding="utf-8")

required_snippets = [
    "$encode = static function ($value) {",
    "return Html::encode((string) $value);",
    'src="<?= $encode($model->armada_qr_code_link) ?>"',
    'src="<?= $encode($model->restaurant->getRestaurantLogoUrl()) ?>"',
    "<?= $encode($model->restaurant->name) ?>",
    "<?= $encode($model->customer_name) ?>",
    "<?= $encode($model->customer_phone_number) ?>",
    "<?= $encode($model->special_directions) ?>",
    "<?= $encode($model->recipient_name) ?>",
    "<?= $encode($model->gift_message) ?>",
    "'format' => 'text'",
]

for snippet in required_snippets:
    if snippet not in text:
        fail(f"missing expected escaping guard: {snippet}")

raw_patterns = [
    r'<\?=\s*\$model->armada_qr_code_link\s*\?>',
    r'<\?=\s*\$model->restaurant->getRestaurantLogoUrl\(\)\s*\?>',
    r'<\?=\s*\$model->restaurant->name\s*\?>',
    r'<\?=\s*\$model->customer_name\s*\?>',
    r'<\?=\s*\$model->customer_phone_number\s*\?>',
    r'<\?=\s*\$model->special_directions\s*\?>',
    r'<\?=\s*\$model->recipient_name\s*\?>',
    r'<\?=\s*\$model->gift_message\s*\?>',
    r'<\?=\s*\$model->recipient_phone_number\s*\?>',
    r'^\s*echo\s+\$model->',
    r"'format'\s*=>\s*'raw'",
]

for pattern in raw_patterns:
    if re.search(pattern, text, re.MULTILINE):
        fail(f"found unescaped backend invoice output matching {pattern}")

print("backend order invoice escaping check passed")
