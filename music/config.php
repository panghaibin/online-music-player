<?php
/************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↓↓↓↓↓ ***************/
$netease_cookie = 'os=pc; JSESSIONID-WYYY=sPU%2FfYJssxiO%2BMcrC7tswsA2NUBVOOeAZZKVM8QB2XyIWzIZw%2BT8PBIY6%2Bo5MxKKOhtYmX4siYgwBmvs1HsDMSyaSm6%2FSOxudGBxSg3U3ScA6aEpw1MsUeVuah%2Bg7sTI%2BT%2F9VdaX6%5CdZkJT4O%5C1on%2FweQvV1FTEiA%2Fd0K2Sue4Z%2BuCoI%3A1633456998144; _iuqxldmzr_=32; _ntes_nnid=060c9842bf4e7fe4c1539d9a03d3d118,1633455198170; _ntes_nuid=060c9842bf4e7fe4c1539d9a03d3d118; NMTID=00OhhCz0Qh55jlHAEeCpU2Mur_HpgcAAAF8UYRNTA; WNMCID=dfbasv.1633455199453.01.0; WEVNSM=1.0.0; WM_NI=Fk76s%2Bfwxj16mtwaV%2BW8GAgegqoZt02tP9U4I0E1LxeVmbYwzmMyHzkyGjZFt6S7kV9iRcpdVA0TDjUMsHkXG6VxkCXQg6X6G96CS844OuucJRkE%2FmsbvPRc7zuOdGiBT3M%3D; WM_NIKE=9ca17ae2e6ffcda170e2e6ee8cf77cb2bb8fd7e274909a8ea3d84a878f8bbbf47c818789d1cb4eaa98f9d5e52af0fea7c3b92a86b5f9d7f221a6a9b88bb43eafacb982d52195e889a2e66fa5afa1bbf73cf2aca0dad8408bf08bd2cf74a78aa383dc7dbab3839acb72a9a7898bd94eb8ea9dd8ea5efbb79e84f370969f00b5c7629793bda9ea5485ea9a99ea499c8fa9d7db3e85e7faa4d234f8ec8196f8698a8ea5a6ae67b1bebab1d169f5a6bbb0e74dab9e9cd4d037e2a3; WM_TID=BKL2cBncYGNEFUEQQUZqoWdSwXRiW75P';
/************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↑↑↑↑↑ ***************/

/**
 * cookie 获取及使用方法见
 * https://github.com/metowolf/Meting/wiki/special-for-netease
*/

//**********OSS 初始化开始**********

$accessKeyId = "填写OSS的 accessKeyId";
$accessKeySecret = "填写OSS的 accessKeySecret";
$endpoint = "填写OSS的 endpoint URL 如 thttp://oss-cn-shenzhen.aliyuncs.com";
$bucket = "填写OSS的 bucket";

//**********OSS 初始化结束**********

$urls = [
    ['1294378245', '1/20190617181039_JQ8JytZb_Imagine%20Dragons%20-%20Natural.mp3'],
    ['19292984', '1/20190617194014_aAPUMNX0_Taylor%20Swift%20-%20Love%20Story.mp3'],
//    根据实际情况，在此添加更多......
];
