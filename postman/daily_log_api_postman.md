# Daily Log API Documentation

## Add Daily Log Entry

**Endpoint:** `POST /api/daily-log/add`

### Request Parameters (Body: form-data)

| Key | Type | Description | Required |
| :--- | :--- | :--- | :--- |
| `home_id` | Integer | ID of the home/location | Yes |
| `user_id` | Integer | ID of the staff member adding the log | Yes |
| `date` | Date | Date of the entry (YYYY-MM-DD) | Yes |
| `entry_type_id` | Integer | ID of the sub-category/entry type | Yes |
| `visitor_name` | String | Name of the visitor | Yes |
| `org_company` | String | Organization or Company name | No |
| `client_id` | Integer | ID of the related client/service user | No |
| `arrival_time` | Time | Arrival time (HH:mm) | No |
| `departure_time` | Time | Departure time (HH:mm) | No |
| `purpose_visit` | String | Reason for the visit | No |
| `notes` | String | Additional notes or observations | No |
| `available_for_overtime`| Boolean | 1 for "Follow-up action required", 0 otherwise | No |
| `follow_details` | String | Details for the follow-up action | No |
| `accompanyingstaff_id`| String/Array| IDs of accompanying staff (comma separated: "2,3") | No |

### Example Request (JSON representation)
```json
{
    "home_id": 1,
    "user_id": 10,
    "date": "2026-05-08",
    "entry_type_id": 1,
    "visitor_name": "John Smith",
    "org_company": "NHS Social Services",
    "client_id": 5,
    "arrival_time": "10:30",
    "departure_time": "11:45",
    "purpose_visit": "Monthly Assessment",
    "notes": "The visitor discussed the care plan with the client.",
    "available_for_overtime": 1,
    "follow_details": "Update care plan by next week.",
    "accompanyingstaff_id": "12,15"
}
```

### Example Response (Success)
```json
{
    "success": true,
    "message": "Daily log added successfully.",
    "data": {
        "id": 156,
        "home_id": "1",
        "user_id": "10",
        "date": "2026-05-08",
        "visitor_name": "John Smith",
        "entry_type_id": "1",
        "org_company": "NHS Social Services",
        "purpose_visit": "Monthly Assessment",
        "client_id": "5",
        "arrival_time": "10:30",
        "departure_time": "11:45",
        "notes": "The visitor discussed the care plan with the client.",
        "available_for_overtime": 1,
        "follow_details": "Update care plan by next week.",
        "updated_at": "2026-05-08T10:50:00.000000Z",
        "created_at": "2026-05-08T10:50:00.000000Z"
    }
}
```

### Example Response (Validation Error)
```json
{
    "success": false,
    "message": "The visitor name field is required."
}
```
