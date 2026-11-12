from pathlib import Path
import hashlib

ROOT = Path(r"D:\Projects\HCSDLDPT\source")

MISSING_FILE = Path("wav_missing_metadata.txt")


def sha256(path):
    h = hashlib.sha256()

    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)

    return h.hexdigest()


paths = [
    line.strip()
    for line in MISSING_FILE.read_text(encoding="utf-8").splitlines()
    if line.strip()
]

print("\n===== KIEM TRA CAC FILE BIEN THE -2 =====")

for relative in paths:
    filename = Path(relative).name

    if not filename.endswith("-2.wav"):
        continue

    variant = ROOT / relative
    original_name = filename[:-6] + ".wav"
    original = variant.with_name(original_name)

    print("\n--------------------------------")
    print("File bien the:", relative)

    if not variant.exists():
        print("KHONG TIM THAY FILE BIEN THE")
        continue

    print("Dung luong bien the:", variant.stat().st_size)

    if not original.exists():
        print("KHONG CO FILE GOC:", original_name)
        continue

    print("Dung luong file goc:", original.stat().st_size)

    hash_variant = sha256(variant)
    hash_original = sha256(original)

    if hash_variant == hash_original:
        print("KET LUAN: HAI FILE GIONG HET NHAU")
    else:
        print("KET LUAN: HAI FILE CO NOI DUNG KHAC NHAU")

print("\n===== KIEM TRA HOAN TAT =====")