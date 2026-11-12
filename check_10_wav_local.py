from pathlib import Path

ROOT = Path(r"D:\Projects\HCSDLDPT\source")

files = {
    "đĩa": "Đ/d-ia4.wav",
    "địa": "Đ/d-ia5.wav",
    "đưa": "Đ/d-uua0.wav",
    "hát": "H/hat1.wav",
    "hàu": "H/hau2.wav",
    "hẳn": "H/haan3.wav",
    "phen": "P/phen0.wav",
    "quết": "Q/que+t1.wav",
    "quật": "Q/qua+t5.wav",
    "sòn": "S/son2.wav",
}

print("===== KIEM TRA WAV LOCAL =====")

for word, relative_path in files.items():
    path = ROOT / relative_path

    if path.exists():
        print(f"[OK] {word}: {relative_path} ({path.stat().st_size} bytes)")
    else:
        print(f"[THIEU] {word}: {relative_path}")