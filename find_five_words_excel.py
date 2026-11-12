from openpyxl import load_workbook
from pathlib import Path

EXCEL_FILE = Path(
    r"D:\Projects\HCSDLDPT\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa.xlsx"
)

KEYWORDS = [
    "bau",
    "naac",
    "ne+m",
    "tro",
    "d-it",
]

wb = load_workbook(EXCEL_FILE, data_only=True)

print("\n===== TIM DONG LIEN QUAN TRONG EXCEL =====")

for ws in wb.worksheets:
    for row_index, row in enumerate(
        ws.iter_rows(values_only=True), start=1
    ):
        values = [
            str(v).strip()
            for v in row
            if v is not None
        ]

        joined = " | ".join(values).lower()

        if any(keyword in joined for keyword in KEYWORDS):
            print("\n--------------------------------")
            print("Sheet:", ws.title)
            print("Dong:", row_index)

            for col_index, value in enumerate(row, start=1):
                if value is not None:
                    print(f"Cot {col_index}: {value}")