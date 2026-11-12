
from openpyxl import load_workbook
from pathlib import Path

BASE_DIR = Path(r"D:\Projects\HCSDLDPT\WhaleVoice")

EXCEL_FILE = Path(
    r"D:\Projects\HCSDLDPT\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa.xlsx"
)

MISSING_FILE = BASE_DIR / "wav_missing_metadata.txt"


def normalize(value):
    return str(value).strip().lower()


# Đọc danh sách WAV cần kiểm tra
wav_files = [
    line.strip()
    for line in MISSING_FILE.read_text(encoding="utf-8").splitlines()
    if line.strip()
]

# Đọc Excel
wb = load_workbook(EXCEL_FILE, data_only=True)

# Lập danh sách các ô trong Excel có giá trị
excel_entries = []

for ws in wb.worksheets:
    for row_index, row in enumerate(
        ws.iter_rows(values_only=True), start=1
    ):
        for col_index, value in enumerate(row, start=1):
            if value is not None:
                excel_entries.append({
                    "sheet": ws.title,
                    "row": row_index,
                    "col": col_index,
                    "value": normalize(value),
                    "original": value
                })

print("\n===== DOI CHIEU 35 WAV VOI EXCEL =====")

for wav in wav_files:
    filename = Path(wav).name
    stem = Path(filename).stem

    # Nếu file có hậu tố -2 thì kiểm tra cả tên đầy đủ
    # và tên gốc bỏ hậu tố -2.
    candidates = [normalize(stem)]

    if stem.lower().endswith("-2"):
        candidates.append(normalize(stem[:-2]))

    matches = [
        entry for entry in excel_entries
        if entry["value"] in candidates
    ]

    print("\n" + "=" * 55)
    print("WAV:", wav)
    print("Ten dang doi chieu:", ", ".join(candidates))

    if matches:
        print("KET QUA: TIM THAY")

        for match in matches:
            print(
                f"Sheet: {match['sheet']} | "
                f"Dong: {match['row']} | "
                f"Cot: {match['col']} | "
                f"Gia tri: {match['original']}"
            )

            # In toàn bộ dòng để xem từ tiếng Việt bên cạnh
            ws = wb[match["sheet"]]
            row_values = [
                value for value in
                next(ws.iter_rows(
                    min_row=match["row"],
                    max_row=match["row"],
                    values_only=True
                ))
                if value is not None
            ]

            print("Noi dung dong:", " | ".join(map(str, row_values)))
    else:
        print("KET QUA: CHUA TIM THAY MA FILE CHINH XAC")

print("\n===== HOAN TAT DOI CHIEU EXCEL =====")
