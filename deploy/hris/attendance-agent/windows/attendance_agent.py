import csv
import json
import shutil
import time
from datetime import datetime
from pathlib import Path
from urllib import request, error


def load_config():
    config_path = Path(__file__).with_name("config.json")
    if not config_path.exists():
        raise FileNotFoundError("config.json belum ada. Copy dari config.example.json lalu isi token agent.")

    with config_path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def read_csv_events(path, mapping):
    events = []

    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        for row in reader:
            events.append({
                "employee_identifier": row.get(mapping["employee_identifier"], "").strip(),
                "employee_name": row.get(mapping.get("employee_name", ""), "").strip(),
                "event_time": row.get(mapping["event_time"], "").strip(),
                "event_type": row.get(mapping["event_type"], "clock_in").strip() or "clock_in",
                "source_device": row.get(mapping.get("source_device", ""), "").strip(),
                "raw_payload": row,
            })

    return [event for event in events if event["employee_identifier"] and event["event_time"]]


def post_payload(endpoint, payload):
    body = json.dumps(payload).encode("utf-8")
    req = request.Request(
        endpoint,
        data=body,
        headers={"Content-Type": "application/json", "Accept": "application/json"},
        method="POST",
    )

    with request.urlopen(req, timeout=60) as response:
        return response.status, response.read().decode("utf-8")


def safe_move(source, folder):
    folder.mkdir(parents=True, exist_ok=True)
    target = folder / source.name
    if target.exists():
        target = folder / f"{source.stem}-{datetime.now().strftime('%Y%m%d%H%M%S')}{source.suffix}"
    shutil.move(str(source), str(target))
    return target


def process_file(path, config):
    events = read_csv_events(path, config["csv_mapping"])
    if not events:
        safe_move(path, Path(config["processed_folder"]))
        return "skipped-empty"

    payload = {
        "connector_code": config["connector_code"],
        "agent_token": config["agent_token"],
        "agent_version": config.get("agent_version", "0.1.0"),
        "batch_id": path.stem,
        "machine_code": config.get("machine_code"),
        "source_type": config.get("source_type", "csv_folder"),
        "source_reference": str(path),
        "events": events,
    }

    try:
        status, response_body = post_payload(config["hris_endpoint"], payload)
        if 200 <= status < 300:
            safe_move(path, Path(config["processed_folder"]))
            return f"synced {len(events)} row(s): {response_body}"
    except error.URLError as exc:
        safe_move(path, Path(config["retry_folder"]))
        return f"queued-retry: {exc}"

    safe_move(path, Path(config["retry_folder"]))
    return "queued-retry"


def main():
    config = load_config()
    csv_folder = Path(config["csv_folder"])
    retry_folder = Path(config["retry_folder"])
    poll_seconds = int(config.get("poll_seconds", 30))

    csv_folder.mkdir(parents=True, exist_ok=True)
    retry_folder.mkdir(parents=True, exist_ok=True)

    print("Kurmigo HRIS Local Attendance Agent started.")
    while True:
        files = list(retry_folder.glob("*.csv")) + list(csv_folder.glob("*.csv"))
        for file_path in files:
            print(datetime.now().isoformat(timespec="seconds"), file_path, process_file(file_path, config))
        time.sleep(poll_seconds)


if __name__ == "__main__":
    main()
