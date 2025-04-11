# hotel-booking-system
[CS5281 25Spring] Hotel Booking System - Group 2

.gitkeep是空文件夹占位符，文件夹下有文件后可以自行删除

## 文件结构
hotel-booking/
│
├── assets/                  # 静态资源文件夹
│   ├── css/
│   │   └── style.css        # 主样式表
│   ├── js/                  # JavaScript文件（可扩展）
│   └── images/              # 图片存储（目前使用placeholder）
│
├── includes/                # 共享组件和功能
│   ├── header.php           # 页眉组件 
│   ├── footer.php           # 页脚组件
│   ├── db.php               # 数据操作函数
│   └── auth.php             # 用户认证功能
│
├── admin/                   # 管理员功能
│   ├── index.php            # 管理员仪表板
│   └── bookings.php         # 预订管理页面
│
├── data/                    # 数据存储（文本文件）
│   ├── users.txt            # 用户数据
│   ├── rooms.txt            # 房间数据
│   └── bookings.txt         # 预订数据
│
├── index.php                # 网站首页
├── search.php               # 搜索结果页
├── room.php                 # 房间详情页
├── booking.php              # 预订流程页
├── confirmation.php         # 预订确认页
├── my-trips.php             # 我的旅程页
├── profile.php              # 个人资料页
├── login.php                # 登录页
├── register.php             # 注册页
└── logout.php               # 登出处理

