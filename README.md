# PHP File Manager

基于 Tiny File Manager 的轻量级 PHP 文件管理器，支持用户认证、在线编辑、文件上传下载等功能。

## 功能特性

- **一键安装** - 首次访问自动进入安装向导，无需手动配置
- **用户管理** - 图形化用户管理界面，轻松添加、删除、修改用户
- **用户认证系统** - 支持多用户登录，基于密码哈希的安全认证
- **文件管理** - 上传、下载、创建、删除、重命名、复制、移动文件和文件夹
- **在线编辑** - 支持代码高亮和在线编辑功能（基于 Ace Editor）
- **文件预览** - 支持图片、文本、代码等多种文件格式预览
- **用户权限** - 支持只读用户和用户专属目录配置
- **响应式设计** - 基于 Bootstrap 5 的现代化界面
- **中文支持** - 默认使用中文界面
- **Docker 支持** - 提供 Docker Compose 快速部署

## 系统要求

- PHP 5.5+
- 支持的 Web 服务器：Apache、Nginx 等
- 可选：Docker 和 Docker Compose

## 快速开始

### 方式一：一键安装（推荐）

1. 克隆仓库并配置 Web 服务器
   ```bash
   git clone https://github.com/xiangxisheng/php-file-manager.git
   cd php-file-manager
   # 配置 Web 服务器指向 wwwroot 目录
   ```

2. 访问网站，系统会自动跳转到安装页面
   - 首次访问会自动检测 `.auth_users` 文件
   - 如果不存在，自动跳转到 `install.php` 安装向导
   - 按照页面提示创建管理员账户即可

3. 安装完成后自动跳转到登录页面

### 方式二：Docker 部署

```bash
# 克隆仓库
git clone https://github.com/xiangxisheng/php-file-manager.git
cd php-file-manager

# 启动服务
docker-compose up -d

# 访问 http://localhost:8080，首次访问会进入安装向导
```

### 方式三：手动创建用户文件

如果需要手动创建用户文件：

```bash
cd php-file-manager/wwwroot
# 创建 .auth_users 文件，添加初始用户
echo '{"admin":"$2y$10$/K.hjNr84lLNDt8fTXjoI.DBp6PpeyoJ.mGwrrLuCZfAwfSAGqhOW"}' > .auth_users
```
> 上面的示例创建了一个用户名为 `admin`，密码为 `admin@123` 的账户

## 用户管理

### 安装向导

首次访问系统时，如果 `.auth_users` 文件不存在，会自动跳转到安装向导页面（`install.php`）：

1. 输入管理员用户名（至少 3 个字符）
2. 设置管理员密码（至少 6 个字符）
3. 确认密码
4. 点击"开始安装"

安装完成后会自动创建 `.auth_users` 文件并跳转到登录页面。

### 用户管理功能

登录后，可以通过 `user_manager.php` 页面管理用户：

**访问方式**：
- 直接访问：`http://your-domain/user_manager.php`
- 需要先登录系统才能访问

**功能包括**：
- **添加用户** - 创建新用户账户
- **修改密码** - 修改任何用户的密码
- **删除用户** - 删除不需要的用户（不能删除当前登录用户）
- **用户列表** - 查看所有用户，当前登录用户会显示标记

### 手动管理用户（高级）

如果需要手动管理用户，可以直接编辑 `wwwroot/.auth_users` 文件。

**文件格式**（JSON）：
```json
{
  "username1": "password_hash1",
  "username2": "password_hash2"
}
```

**生成密码哈希**：
```bash
# 使用 PHP 命令行
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```

**在线工具**：
访问 [Tiny File Manager 密码生成器](https://tinyfilemanager.github.io/docs/pwd.html)

### 配置只读用户

在 `wwwroot/config.php` 中设置只读用户：

```php
$readonly_users = array(
    'readonly',  // 将用户名添加到此数组
    'guest'
);
```

只读用户可以浏览和下载文件，但不能上传、删除或修改文件。

## 配置说明

### 配置文件

主要配置文件位于 `wwwroot/config.php`：

- **用户认证**：从 `.auth_users` 文件读取用户信息（JSON 格式）
- **语言设置**：默认为简体中文 (`zh-CN`)
- **主题**：支持亮色和暗色主题
- **用户目录**：自动扫描 `users` 目录为每个用户创建专属空间
- **上传限制**：可配置最大上传文件大小
- **访问控制**：支持 IP 白名单和黑名单

### 用户目录

系统会自动扫描 `users` 目录，为每个子目录创建对应的用户专属空间。例如：

```
users/
├── alice/    # alice 用户的专属目录
└── bob/      # bob 用户的专属目录
```

在 `config.php` 中会自动为这些目录创建用户映射。

### 修改密码

登录后，点击右上角用户菜单，选择"修改密码"功能即可修改当前用户密码。修改后的密码会自动更新到 `.auth_users` 文件中。

## 目录结构

```
php-file-manager/
├── wwwroot/              # Web 根目录
│   ├── index.php         # 主程序文件
│   ├── config.php        # 配置文件
│   ├── install.php       # 安装向导页面
│   ├── user_manager.php  # 用户管理页面
│   ├── .auth_users       # 用户认证数据（自动生成，git 已忽略）
│   ├── css/              # 样式文件
│   ├── js/               # JavaScript 文件
│   └── fonts/            # 字体文件
├── users/                # 用户专属目录
├── etc/                  # 其他配置
├── script/               # 脚本文件
├── docker-compose.yml    # Docker 编排配置
└── README.md             # 本文档
```

## 安全建议

1. **使用强密码**：安装时设置强密码，避免使用简单密码
2. **定期修改密码**：定期通过用户管理功能修改密码
3. **限制访问**：建议使用 IP 白名单限制访问来源
4. **HTTPS**：生产环境建议启用 HTTPS
5. **文件权限**：系统会自动设置 `.auth_users` 文件权限为 0600（仅所有者可读写）
6. **定期更新**：保持系统和依赖库更新到最新版本
7. **删除安装文件**：如果需要更高安全性，安装完成后可以删除或重命名 `install.php`

## 技术栈

- **后端**：PHP
- **前端**：Bootstrap 5, jQuery, Font Awesome
- **编辑器**：Ace Editor
- **代码高亮**：Highlight.js
- **文件上传**：Dropzone.js

## 更新日志

### 最新版本
- **一键安装功能** - 首次访问自动检测并跳转到安装向导
- **用户管理界面** - 新增图形化用户管理页面，支持添加、删除、修改用户
- **自动文件检测** - 自动检测 `.auth_users` 文件是否存在
- **安全增强** - 自动设置用户文件权限

### 之前版本
- 默认语言改为中文
- 增加"修改密码"功能
- 添加用户专属目录支持
- 优化配置文件结构
- 添加 Docker 支持

## 许可证

本项目基于 Tiny File Manager 开发。

## 相关链接

- [Tiny File Manager 官网](https://tinyfilemanager.github.io)
- [Tiny File Manager GitHub](https://github.com/prasathmani/tinyfilemanager)

## 贡献

欢迎提交 Issue 和 Pull Request！

## 联系方式

- **作者 QQ**: 309385018
- **交流群**: 添加作者 QQ 后邀请加入（私密群）

如有问题或建议：
- 在 GitHub 上提交 Issue
- 添加作者 QQ: 309385018，备注来意
- 加群交流：请先添加作者 QQ，通过后会邀请您加入技术交流群
