from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
MODEL = ROOT / "frontend" / "models" / "PasswordResetRequestForm.php"

source = MODEL.read_text(encoding="utf-8")

for forbidden in [
    "die(var_dump($agent->errors))",
    "var_dump($agent->errors)",
    "print_r($agent->errors",
]:
    if forbidden in source:
        raise SystemExit(f"Raw agent password-reset errors are exposed via {forbidden!r}")

if "Failed to save frontend agent password reset token." not in source:
    raise SystemExit("Missing operator log message for password reset token save failure")

if "Yii::error([" not in source or "'errors' => $agent->errors" not in source:
    raise SystemExit("Password reset token save failures should be logged for operators")

if "return false;" not in source:
    raise SystemExit("Password reset token save failures must preserve false return behavior")
