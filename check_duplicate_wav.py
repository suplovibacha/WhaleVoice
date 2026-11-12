
from pathlib import Path
import hashlib

SOURCE = Path(r"D:\Projects\HCSDLDPT\source")
MISSING = Path("wav_missing_metadata.txt")


def sha256(path):
    h = hashlib.sha256()

    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)

    return h.hexdigest()


print("===== KIEM TRA FILE CO HAU TO -2 =====")

if not MISSING.exists():
    print("Khong tim thay wav_missing_metadata.txt")
    raise SystemExit

count = 0

for line in MISSING.read_text(encoding="utf-8").splitlines():
    relative = line.strip().replace("\\", "/")

    if not relative:
        continue

    path = SOURCE / relative
    filename = path.name

    if not filename.lower().endswith("-2.wav"):
        continue

    original_name = filename[:-6] + ".wav"
    original = path.with_name(original_name)

    count += 1

    print("\nFile:", relative)

    if not path.exists():
        print("KHONG TIM THAY FILE")
        continue

    if not original.exists():
        print("KHONG CO FILE GOC:", original.name)
        continue

    print("File goc:", original.name)
    print("Dung luong file -2:", path.stat().st_size)
    print("Dung luong file goc:", original.stat().st_size)

    if sha256(path) == sha256(original):
        print("KET LUAN: TRUNG KHOP HOAN TOAN")
    else:
        print("KET LUAN: NOI DUNG HAI FILE KHAC NHAU")

print("\n===== TONG KET =====")
print("So file -2 da kiem tra:", count)
