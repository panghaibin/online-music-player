Online Music Player
===

基于 [MKOnlineMusicPlayer](https://github.com/mengkunsoft/MKOnlineMusicPlayer) 魔改

项目地址： https://github.com/panghaibin/online-music-player

---

## 魔改新增的功能

 - 后端实现外文歌词翻译合并
   
 - LRC 格式歌词生成下载，采用 ANSI 编码格式，保证兼容性
   
 - 将歌词导出为 PDF 格式并下载，允许以下自定义排版设置：

    - 自定义标题
   
     - 字号：6~20
    
     - 字体：等线 / 微软雅黑 / ~~华康少女体~~
   
     - 分栏设置：不分栏 / 两栏 / 三栏
   
     - 显示歌曲二维码：内容为歌曲的主页
   
 - 双击歌词区域，可进入全屏显示
   
 - 墨水屏设备适配 (/music-ink)

 - 可将歌曲播放链接替换为自建阿里云 OSS 的链接，以解决付费歌曲无法解析问题 (/music/config.php)

## 说明
原项目使用到了 [Meting](https://github.com/metowolf/Meting) 进行歌曲的解析，如有需要可手动替换更新。另外WYY的歌曲解析需要 Cookies ，若失效需要手动获取替换，位于 /music/config.php

LRC 格式歌词生成下载的功能用到了一个字符编码转换类，目前没有找到原始出处

歌词导出为 PDF 格式并下载功能用到了 [TCPDF](https://tcpdf.org/) 

该代码魔改于 2018 年，~~写得很烂就是了，目前项目代码依赖 Bug 运行~~
