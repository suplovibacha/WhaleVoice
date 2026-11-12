
from pathlib import Path
import csv
import wave
import hashlib

SOURCE = Path(r"D:\Projects\HCSDLDPT\source")
MISSING = Path("wav_missing_metadata.txt")
OUTPUT = Path("wav_review_report.csv")


def get_hash(path):
    h = hashlib.sha256()

    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)

    return h.hexdigest()


def get_audio_info(path):
    try:
        with wave.open(str(path), "rb") as wav:
            return {
                "duration": round(
                    wav.getnframes() / wav.getframerate(), 3
                ),
                "sample_rate": wav.getframerate(),
                "channels": wav.getnchannels(),
                "sample_width": wav.getsampwidth(),
                "error": ""
            }
    except Exception as e:
        return {
            "duration": "",
            "sample_rate": "",
            "channels": "",
            "sample_width": "",
            "error": str(e)
        }


if not MISSING.exists():
    print("Khong tim thay:", MISSING)
    raise SystemExit

rows = []

files = [
    line.strip().replace("\\", "/")
    for line in MISSING.read_text(encoding="utf-8").splitlines()
    if line.strip()
]

for relative in files:
    path = SOURCE / relative
    name = path.name
    has_suffix = path.stem.endswith("-2")

    original = None

    if has_suffix:
        original_name = path.stem[:-2] + path.suffix
        original = path.with_name(original_name)

    info = get_audio_info(path)

    row = {
        "missing_file": relative,
        "file_exists": path.exists(),
        "size_bytes": path.stat().st_size if path.exists() else "",
        "duration_seconds": info["duration"],
        "sample_rate": info["sample_rate"],
        "channels": info["channels"],
        "sample_width": info["sample_width"],
        "paired_original": "",
        "original_exists": "",
        "original_size_bytes": "",
        "original_duration_seconds": "",
        "same_content": "",
        "status": ""
    }

    if info["error"]:
        row["status"] = "Loi doc WAV: " + info["error"]

    elif not path.exists():
        row["status"] = "Khong tim thay file"

    elif not has_suffix:
        row["status"] = "Khong co hau to -2, can doi chieu Excel"

    elif original.exists():
        original_info = get_audio_info(original)

        row["paired_original"] = str(
            original.relative_to(SOURCE)
        ).replace("\\", "/")

        row["original_exists"] = True
        row["original_size_bytes"] = original.stat().st_size
        row["original_duration_seconds"] = original_info["duration"]

        if not original_info["error"]:
            row["same_content"] = (
                get_hash(path) == get_hash(original)
            )

        row["status"] = (
            "Trung noi dung"
            if row["same_content"] is True
            else "Hai ban ghi khac nhau, can nghe chon"
        )

    else:
        row["paired_original"] = str(
            original.relative_to(SOURCE)
        ).replace("\\", "/")

        row["original_exists"] = False
        row["status"] = "Khong co file goc, can kiem tra"

    rows.append(row)

fields = list(rows[0].keys()) if rows else []

with OUTPUT.open("w", newline="", encoding="utf-8-sig") as f:
    writer = csv.DictWriter(f, fieldnames=fields)
    writer.writeheader()
    writer.writerows(rows)

print("\n===== HOAN THANH DOI CHIEU =====")
print("Tong file:", len(rows))
print("Bao cao:", OUTPUT.resolve())

for row in rows:
    print(
        row["missing_file"],
        "=>",
        row["status"]
    )
