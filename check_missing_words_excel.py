
import pandas as pd
from pathlib import Path
import re

EXCEL_PATH = Path(
    r"D:\Projects\HCSDLDPT\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa.xlsx"
)

MISSING_PATH = Path("wav_missing_metadata.txt")


def normalize(value):
    """Chuẩn hóa tên file để so sánh."""
    return str(value).strip().lower().replace("\\", "/")


if not EXCEL_PATH.exists():
    print("KHONG TIM THAY FILE EXCEL:", EXCEL_PATH)
    raise SystemExit

if not MISSING_PATH.exists():
    print("KHONG TIM THAY:", MISSING_PATH)
    raise SystemExit

# Đọc danh sách 41 file WAV
missing_files = {}

for line in MISSING_PATH.read_text(encoding="utf-8").splitlines():
    line = line.strip()

    if line:
        basename = Path(line).name.lower()
        missing_files.setdefault(basename, []).append(line)

excel = pd.ExcelFile(EXCEL_PATH)

found = set()

print("\n===== DOI CHIEU 41 FILE WAV VOI TOAN BO EXCEL =====")

for sheet in excel.sheet_names:

    # header=None để không coi dòng đầu tiên là tên cột
    df = pd.read_excel(
        EXCEL_PATH,
        sheet_name=sheet,
        header=None,
        dtype=str
    )

    for row_index, row in df.iterrows():

        for col_index, value in enumerate(row):

            if pd.isna(value):
                continue

            cell = normalize(value)

            # Tìm tên file WAV trong nội dung ô
            for basename, paths in missing_files.items():

                # Tách các tên file xuất hiện trong ô
                filenames = re.findall(
                    r"[^\\/\s,;]+\.wav",
                    cell
                )

                if basename in filenames or cell == basename:

                    found.add(basename)

                    print("\nTim thay file:")
                    print("Sheet:", sheet)
                    print("Dong Excel:", row_index + 1)
                    print("Cot Excel:", col_index + 1)
                    print("Gia tri o:", value)

                    # In toàn bộ nội dung dòng để xác định từ tương ứng
                    print("Noi dung dong:")

                    for i, item in enumerate(row):
                        if pd.notna(item) and str(item).strip():
                            print(f"  Cot {i + 1}: {item}")

                    break

print("\n===== TONG KET =====")
print("Tong file can doi chieu:", len(missing_files))
print("Tim thay trong Excel:", len(found))
print("Chua tim thay:", len(missing_files) - len(found))

print("\n===== FILE CHUA TIM THAY TRONG EXCEL =====")

for basename in sorted(set(missing_files.keys()) - found):
    for path in missing_files[basename]:
        print(path)
