
<p align="center">
<a href="https://github.com/vuejs/vue">
    <img src="https://img.shields.io/badge/vue-3.2.16-brightgreen.svg" alt="vue">
  </a>
  <a href="https://github.com/ElemeFE/element">
    <img src="https://img.shields.io/badge/element--plus-1.1.0-brightgreen.svg" alt="element-ui">
  </a>
  <a href="https://github.com/ElemeFE/element">
    <img src="https://img.shields.io/badge/vite-2.6.4-brightgreen.svg" alt="element-ui">
  </a>
  <a href="https://github.com/ElemeFE/element">
    <img src="https://img.shields.io/badge/laravel/framework-8.6.5-blue.svg" alt="element-ui">
  </a>
  <a href="https://github.com/lin-xin/vue-manage-system/blob/master/LICENSE">
    <img src="https://img.shields.io/github/license/mashape/apistatus.svg" alt="license">
  </a>
</p>

## 🎯目的：
大檔案上傳時，一旦遇到網路不穩或斷線，就會導致檔案需要全部重新上傳，該項目透過Vue3 + Laravel8實現斷點續傳，提供學習及參考！

---

## 目錄：

#### 1. 安裝說明

#### 2. 系統展示

#### 3. 技術說明

#### 4. 其它補充

#### 5. 未來可優化方向

---

## 1. 安裝說明：

#### 前端

技術：vue3, vite, spark-md5, element-plus, axios

- use npm：
  ```
  npm install
  npm run dev
  ```
- use yarn：
  ```
  yarn install
  yarn dev
  ```
  **_(備註：前端預設會連線到後端 localhost:8001（預設） 進行請求，請依照需求進行更改，更改說明放至 3. 技術說明中）_**

#### 後端

技術：laravel8

- 使用 composer

  ```
      composer install
      php artisan serve
  ```

#### 測試環境

瀏覽器：Chrome96
伺服器：Apache2.4.32
PHP：Vesion 7.4+
傳輸協議：http1

---

## 2. 展示

1. 大檔案分割及斷點上傳
![](https://i.imgur.com/LhgO5kG.gif)

2. 後端接收檔案及合併
![](https://i.imgur.com/jAM8QwV.gif)

3. 逐步上傳（大檔）
![](https://i.imgur.com/XqAza2T.gif)

4. 並行上傳（小檔）
![](https://i.imgur.com/ooIW4TU.gif)

---

## 3. 技術說明

#### 系統流程圖

![](https://i.imgur.com/PKemX38.png)

#### 後端 API（詳細說明程式碼附註解）

![](https://i.imgur.com/jfukbxb.png)

1. hash_check：檢查 Hash 過的檔案是否存在伺服器上
2. chunks_upload：切片檔案上傳
3. chunks_merge：切片檔案全部上傳後進行合併，並刪除所有切片檔案

#### 前端（詳細參閱程式碼附註解）

![](https://i.imgur.com/9kq62Cr.png)

![](https://i.imgur.com/3TmNLvY.png)

![](https://i.imgur.com/7rkWG5N.png)

前端開發環境請求網址修改，Config/index.js：
```javascript=
const EnvConfig = {
    // 開發環境
    dev: {
        baseApi: 'http://localhost:8001/api', <---開發環境請求網址
    },
    // 正式環境
    prod: {
        baseApi: '/api',
    },
}
    
```

---

## 4. 其它補充

1. Hash 方式改變

### 方式一（不採用）：加載每個檔案內容並進行 Hash。
不採用原因：高負載的 CPU，及緩慢的處理進度（以採用現行最快的 Spark-md5 進行處理且有切片，非大檔加載）

（下圖為 CPU 使用率，整個網頁會非常的卡頓）
![](https://i.imgur.com/yDWiSql.png)

實際運行
![](https://i.imgur.com/WewQanc.gif)

---

### 方式二（目前採用）：針對檔名進行 Hash 加密

（下圖為改善後）
![](https://i.imgur.com/W2deuTH.png)

實際運行
![](https://i.imgur.com/lvKmecR.gif)

---

## 5. 未來可優化方向

1. 請求重試功能（當切片上傳失敗時，可再次進行上傳，直到 N 次失敗，提示使用者）
2. 嘗試使用 Http2（Why use? because...Browsers impose a per-domain limit of 6-8 connections when using HTTP/1.1, depending on the browser implementation.With HTTP/2, browsers open only 1 connection per domain. However, thanks to the multiplexing feature of the HTTP/2 protocol, the number of concurrent requests per domain is not limited to 6-8, but it is virtually unlimited.）看看能不能改善請求數量及效率問題（待實驗）
3. more...

---

### Reference

- [Node + js實現大文件分片上傳基本原理及實踐(一)](https://www.cnblogs.com/tugenhua0707/p/11246860.html)

- [JS 實現大文件併發上傳](https://developer.51cto.com/art/202106/664707.htm)

- [身為 JS 開發者，你應該要知道的記憶體管理機制](https://medium.com/starbugs/%E8%BA%AB%E7%82%BA-js-%E9%96%8B%E7%99%BC%E8%80%85-%E4%BD%A0%E4%B8%8D%E8%83%BD%E4%B8%8D%E7%9F%A5%E9%81%93%E7%9A%84%E8%A8%98%E6%86%B6%E9%AB%94%E7%AE%A1%E7%90%86%E6%A9%9F%E5%88%B6-d9db2fd66f8)

- [Expected performance of MD5 calculation in javascript?](https://stackoverflow.com/questions/28845659/expected-performance-of-md5-calculation-in-javascript)

- [They All Shall Pass: A Guide to Handling Large File Uploads](https://uploadcare.com/blog/handling-large-file-uploads/)

more...
