// 全域變數
let currentItemId = null; // 當前編輯的項目ID

// DOM 元素
const itemModal = document.getElementById('itemModal');
const confirmModal = document.getElementById('confirmModal');
const itemForm = document.getElementById('itemForm');
const addItemBtn = document.getElementById('addItemBtn');
const refreshBtn = document.getElementById('refreshBtn');
const inventoryTableBody = document.getElementById('inventoryTableBody');
const alertsContainer = document.getElementById('alertsContainer');
const messageContainer = document.getElementById('messageContainer');

// 事件監聽器設定
document.addEventListener('DOMContentLoaded', () => {
    // 載入初始資料
    load_inventory();
    
    // 新增按鈕點擊事件
    addItemBtn.addEventListener('click', () => {
        open_add_modal();
    });
    
    // 重新整理按鈕點擊事件
    refreshBtn.addEventListener('click', () => {
        load_inventory();
    });
    
    // 表單提交事件
    itemForm.addEventListener('submit', (e) => {
        e.preventDefault();
        save_item();
    });
    
    // 關閉按鈕事件
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', () => {
            itemModal.style.display = 'none';
            confirmModal.style.display = 'none';
        });
    });
});

// 載入庫存資料
async function load_inventory() {
    try {
        const response = await fetch('../kiosk/api/inventory.php');
        const data = await response.json();
        
        if (data.success) {
            display_inventory(data.data);
            check_low_inventory(data.data);
        } else {
            show_message('載入庫存資料失敗：' + data.message, 'error');
        }
    } catch (error) {
        show_message('系統錯誤：' + error.message, 'error');
    }
}

// 顯示庫存資料
function display_inventory(items) {
    inventoryTableBody.innerHTML = '';
    
    items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td class="${item.alerts.is_low ? 'low-stock' : ''}">${item.quantity} ${item.unit}</td>
            <td>${item.unit}</td>
            <td>${item.min_quantity}</td>
            <td>${item.supplier}</td>
            <td>${item.last_updated}</td>
            <td>
                <button onclick="edit_item('${item.id}')" class="btn btn-small btn-primary">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="confirm_delete('${item.id}')" class="btn btn-small btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        inventoryTableBody.appendChild(row);
    });
}

// 檢查低庫存警告
function check_low_inventory(items) {
    alertsContainer.innerHTML = '';
    
    const lowStockItems = items.filter(item => item.alerts.is_low);
    if (lowStockItems.length > 0) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-warning';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <strong>庫存警告：</strong> ${lowStockItems.length} 個項目庫存過低
            <ul>
                ${lowStockItems.map(item => `
                    <li>${item.name}：剩餘 ${item.quantity} ${item.unit}</li>
                `).join('')}
            </ul>
        `;
        alertsContainer.appendChild(alert);
    }
}

// 開啟新增 Modal
function open_add_modal() {
    currentItemId = null;
    document.getElementById('modalTitle').textContent = '新增庫存項目';
    itemForm.reset();
    itemModal.style.display = 'block';
}

// 開啟編輯 Modal
async function edit_item(id) {
    try {
        const response = await fetch('../kiosk/api/inventory.php');
        const data = await response.json();
        
        if (data.success) {
            const item = data.data.find(item => item.id === id);
            if (item) {
                currentItemId = id;
                document.getElementById('modalTitle').textContent = '編輯庫存項目';
                document.getElementById('itemId').value = item.id;
                document.getElementById('itemName').value = item.name;
                document.getElementById('itemQuantity').value = item.quantity;
                document.getElementById('itemUnit').value = item.unit;
                document.getElementById('itemMinQuantity').value = item.min_quantity;
                document.getElementById('itemSupplier').value = item.supplier;
                itemModal.style.display = 'block';
            }
        }
    } catch (error) {
        show_message('載入項目資料失敗：' + error.message, 'error');
    }
}

// 儲存項目
async function save_item() {
    const formData = {
        id: currentItemId,
        name: document.getElementById('itemName').value,
        quantity: parseInt(document.getElementById('itemQuantity').value),
        unit: document.getElementById('itemUnit').value,
        min_quantity: parseInt(document.getElementById('itemMinQuantity').value),
        supplier: document.getElementById('itemSupplier').value
    };

    try {
        const action = currentItemId ? 'update' : 'add';
        const response = await fetch(`../kiosk/api/inventory.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        if (data.success) {
            show_message(data.message, 'success');
            itemModal.style.display = 'none';
            load_inventory();
        } else {
            show_message('儲存失敗：' + data.message, 'error');
        }
    } catch (error) {
        show_message('系統錯誤：' + error.message, 'error');
    }
}

// 確認刪除
function confirm_delete(id) {
    currentItemId = id;
    confirmModal.style.display = 'block';
    
    document.getElementById('confirmDelete').onclick = delete_item;
}

// 刪除項目
async function delete_item() {
    try {
        const response = await fetch(`../kiosk/api/inventory.php?id=${currentItemId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        if (data.success) {
            show_message(data.message, 'success');
            confirmModal.style.display = 'none';
            load_inventory();
        } else {
            show_message('刪除失敗：' + data.message, 'error');
        }
    } catch (error) {
        show_message('系統錯誤：' + error.message, 'error');
    }
}

// 關閉 Modal
function closeModal() {
    itemModal.style.display = 'none';
}

function closeConfirmModal() {
    confirmModal.style.display = 'none';
}

// 顯示訊息
function show_message(message, type = 'info') {
    const messageElement = document.createElement('div');
    messageElement.className = `message message-${type}`;
    messageElement.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    messageContainer.appendChild(messageElement);
    
    // 3秒後自動移除訊息
    setTimeout(() => {
        messageElement.remove();
    }, 3000);
} 