function extract_fields(tag, timestamp, record)

    local store_log = false

    local log_message = record["log"]

    -- Tạo các bảng để lưu trữ các giá trị duy nhất
    local scp_aet_list = {}
    local scu_aet_list = {}
    local scp_list = {}

    -- Dùng biểu thức chính quy để lặp qua tất cả các cặp khớp
    for otherAE, aet in log_message:gmatch(":(%S+)%s*->%s*(%S+)%)") do
        -- Thêm otherAE vào scu_aet_list nếu nó chưa tồn tại
        if not scu_aet_list[otherAE] then
            scu_aet_list[otherAE] = true
            table.insert(scu_aet_list, otherAE)
        end

        -- Thêm aet vào scp_aet_list nếu nó chưa tồn tại
        if not scp_aet_list[aet] then
            scp_aet_list[aet] = true
            table.insert(scp_aet_list, aet)
        end
    end

    for value in log_message:gmatch("Received%s+(%S+)%s+SCP") do
        if not scp_list[value] then
            scp_list[value] = true
            table.insert(scp_list, value)
        end
    end

--     local vn_timestamp = os.date("!%Y-%m-%dT%H:%M:%S", timestamp + 7 * 3600) .. "+07:00"
--
--     -- Thêm trường thời gian mới vào record
--     record["time"] = vn_timestamp

    --Neu log_store = false bo qua log store

    if not store_log then
        if #scp_list == 1 and scp_list[1] == "Store" then
            return -1, timestamp, record -- Trả về -1 để Fluent Bit bỏ qua bản ghi
        end
    end

    if #scp_list > 0 then
        record["queryRetrieve"] = scp_list
    else
        record["queryRetrieve"] = "null"
    end

    if #scp_aet_list > 0 then
        record["scpAET"] = scp_aet_list
    else
        record["scpAET"] = "null"
    end

    if #scu_aet_list > 0 then
        record["scuAET"] = scu_aet_list
    else
        record["scuAET"] = "null"
    end

    records = split_large_log(record)
    -- Trả về record đã cập nhật
    return 1, timestamp, records
end


function split_large_log(record)
    local log_message = record["log"]
    record["log"] = nil
    local max_length = 100000 -- Độ dài tối đa cho mỗi bản ghi log
    local records = {}
--     print(log_message)

--     print("...................",record["scpAET"])
    -- Chia nhỏ log thành các phần không vượt quá max_length
    while #log_message > max_length do
        local part = log_message:sub(1, max_length) -- Lấy phần đầu tiên của log
        log_message = log_message:sub(max_length + 1) -- Cắt bỏ phần đã lấy


        -- Tạo bản ghi mới
        local new_record = {}

        for key, value in pairs(record) do
            new_record[key] = value -- Sao chép tất cả các trường khác
        end

        local vn_timestamp = os.date("!%Y-%m-%dT%H:%M:%S", os.time() + 7 * 3600) .. "+07:00"

        -- Thêm trường thời gian mới vào record
        new_record["time"] = vn_timestamp
--         print(vn_timestamp)
        new_record["log"] = part -- Thay thế log bằng phần đã tách ra

        table.insert(records, new_record) -- Thêm bản ghi mới vào danh sách
    end

    -- Thêm phần còn lại nếu có
    if #log_message > 0 then
        local new_record = {}
        for key, value in pairs(record) do
            new_record[key] = value -- Sao chép tất cả các trường khác
        end
        local vn_timestamp = os.date("!%Y-%m-%dT%H:%M:%S", os.time() + 7 * 3600) .. "+07:00"

        -- Thêm trường thời gian mới vào record
        new_record["time"] = vn_timestamp
        new_record["log"] = log_message -- Thay thế log bằng phần còn lại
        table.insert(records, new_record)
    end

    -- Trả về tất cả các bản ghi đã cập nhật
    return records
end

