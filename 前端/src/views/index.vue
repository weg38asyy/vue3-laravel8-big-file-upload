<template>
    <div>
        <div>
            <el-button-group>
                <el-button>
                    <i class="el-icon-upload2 el-icon--left" size="mini"></i>
                    <input type="file" @change="fileButtons.addFile" />
                </el-button>
                <el-button @click="fileButtons.startUpload"
                    ><i class="el-icon-upload el-icon--left" size="mini"></i
                    >上傳</el-button
                >
            </el-button-group>
            <slot name="header"></slot>
        </div>
    </div>

    <el-card class="box-card">
        載入進度
        <el-progress
            :text-inside="true"
            :stroke-width="18"
            :percentage="fileUpload.fileProgress"
            status="success"
        />
        上傳進度
        <el-progress
            :text-inside="true"
            :stroke-width="18"
            :percentage="fileUpload.progress"
            status="success"
        />
        <div>
            目前分割數：{{ fileUpload.chunkDoneTotal }}/{{
                fileUpload.chunkTotal
            }}
        </div>
        <div>文件大小：{{ fileUpload.fileSize }} {{ fileUpload.fileUnit }}</div>
    </el-card>
    <el-result
        v-if="fileUpload.tips"
        :icon="fileUpload.tips_icon"
        :sub-title="fileUpload.tips"
    ></el-result>
</template>

<script>
import { reactive, onMounted, computed, inject } from 'vue'
import SparkMD5 from 'spark-md5'

export default {
    setup() {
        // Source: main.js
        const $request = inject('$request')
        // 方便後續檔案大小檢視
        const KB = 1024
        const MB = 1024 * KB
        const GB = 1024 * MB
        const blobSlice =
            File.prototype.slice ||
            File.prototype.mozSlice ||
            File.prototype.webkitSlice

        // 檔案上傳設定
        const fileUpload = reactive({
            allowUpload: false, // 文件是否允許上傳狀態（避免使用者在文件尚未計算完Hash時，按下上傳按鈕）
            chunkSizeDefault: false, // 如果切片大小預設啟用，則會吃chunkSize的設定，否則會根據檔案大小進行不同切片大小的切分（詳細看Readme.md的流程圖）
            chunkSize: 5 * MB, // 切片大小設定（可選：5MB、10MB、15MB、20MB）
            chunkTotal: 0, //  切片總數
            chunkDoneTotal: 0, // 上傳成功切片數量
            file: null, // 二進制文件
            fileHash: '', // 文件Hash
            fileName: '', // 文件名稱
            fileSize: '', // 文件大小
            fileUnit: '', // 文件大小單位
            tips_icon: 'info', // 提示Icon
            tips: '尚無資訊', // 提示訊息

            // 檔案載入切割進度條
            fileProgress: 0,

            // 進度條數值計算（用切片來計算，所以切片大小不能過大，否則會看到進度條卡住）
            progress: computed(() => {
                if (fileUpload.chunkTotal === 0) {
                    return 0
                } else {
                    return parseInt(
                        (fileUpload.chunkDoneTotal / fileUpload.chunkTotal) *
                            100,
                        10
                    )
                }
            }),

            // 根據文件大小計算分片數量，並將文件名編成Hash值
            getFile: async () => {
                if (!fileUpload.file) {
                    fileUpload.updateTips(
                        'error',
                        '取得文件失敗，請重新選擇上傳的文件'
                    )
                    return
                }
                fileUpload.calFileInfo(
                    fileUpload.file.name,
                    fileUpload.file.size
                )
                await fileUpload.calFileHash(fileUpload.file)
            },

            // 顯示文件基本信息
            calFileInfo: (fileName, fileSize) => {
                fileUpload.fileName = fileName

                if (fileSize >= GB) {
                    fileUpload.fileSize = parseFloat(fileSize / GB).toFixed(2)
                    fileUpload.fileUnit = 'GB'
                } else if (fileSize >= MB) {
                    fileUpload.fileSize = parseFloat(fileSize / MB).toFixed(2)
                    fileUpload.fileUnit = 'MB'
                } else if (fileSize >= KB) {
                    fileUpload.fileSize = parseFloat(fileSize / KB).toFixed(2)
                    fileUpload.fileUnit = 'KB'
                }
            },

            // 計算文件Hash值
            calFileHash(file) {
                const fileSize = file.size

                // chunkSizeDefault為Flase時，判斷文件大小，若大於10MB以2MB進行切片，大於100MB每次以5MB進行切片，大於500MB每次以10MB進行切片，大於1GB以20MB切片
                if (!fileUpload.chunkSizeDefault) {
                    if (fileSize > 1 * GB) {
                        fileUpload.chunkSize = 20 * MB
                    } else if (fileSize > 500 * MB) {
                        fileUpload.chunkSize = 10 * MB
                    } else if (fileSize > 100 * MB) {
                        fileUpload.chunkSize = 5 * MB
                    } else if (fileSize > 25 * MB) {
                        fileUpload.chunkSize = 2 * MB
                    }
                }

                return new Promise((resolve, reject) => {
                    const chunks = Math.ceil(file.size / fileUpload.chunkSize)
                    fileUpload.chunkTotal = chunks

                    // 針對文件內容計算Hash值，暫時不採取該方案，註解掉
                    // const spark = new SparkMD5.ArrayBuffer()
                    const fileReader = new FileReader()
                    let currentChunk = 0

                    fileUpload.updateTips(
                        'warning',
                        '正在讀取文件計算Hash中，請稍後……'
                    )

                    fileReader.onload = function (e) {
                        // 針對文件內容計算Hash值，暫時不採取該方案，註解掉
                        // spark.append(e.target.result) // Append array buffer
                        // Append array buffer

                        // 目前切片數量
                        currentChunk++
                        console.log(
                            '現在切片：',
                            currentChunk,
                            '；全部切片：',
                            chunks
                        )

                        // 載入進度條更新
                        fileUpload.fileProgress = parseInt(
                            (currentChunk / chunks) * 100,
                            10
                        )

                        // 目前切片數量 尚未超過 切片總數量時
                        if (currentChunk < chunks) {
                            loadNext()
                        } else {
                            fileUpload.chunkTotal = currentChunk
                            // 針對文件內容計算Hash值，暫時不採取該方案，註解掉
                            // const hash = spark.end()
                            // 以文件名稱為主，計算文件的Hash
                            const spark = new SparkMD5()
                            spark.append(fileUpload.fileName)
                            const hash = spark.end()
                            fileUpload.fileHash = hash
                            fileUpload.allowUpload = true

                            fileUpload.updateTips(
                                'success',
                                '文件載入完成，文件的Hash值是' + hash
                            )
                            resolve(hash)
                        }
                    }

                    fileReader.onerror = function () {
                        fileUpload.updateTips(
                            'error',
                            '讀取切片失敗，請重新選擇檔案嘗試'
                        )
                        reject('讀取切片失敗，請重新選擇檔案嘗試')
                    }

                    function loadNext() {
                        let start = currentChunk * fileUpload.chunkSize,
                            end =
                                start + fileUpload.chunkSize >= file.size
                                    ? file.size
                                    : start + fileUpload.chunkSize
                        // 切片讀取(避免檔案過大，瀏覽器直接內存超出上限而崩潰)
                        fileReader.readAsArrayBuffer(
                            blobSlice.call(file, start, end)
                        )
                    }

                    loadNext()
                }).catch(err => {
                    console.log(err)
                })
            },

            // 更新提示訊息
            updateTips: (type, msg) => {
                fileUpload.tips_icon = type
                fileUpload.tips = msg
            },

            // 確認要上傳的文件是否已經存在伺服器，或者需要斷點續傳（中間上傳到一半斷線的狀況）
            async postFileHash() {
                return new Promise((resolve, reject) => {
                    $request
                        .post(
                            '/hash_check',
                            {
                                hash: fileUpload.fileHash,
                                fileName: fileUpload.fileName,
                                chunkSize: fileUpload.chunkSize,
                                total: fileUpload.chunkTotal,
                            },
                            {
                                timeout: 0,
                            }
                        )
                        .then(res => {
                            if (res.status === 'success') {
                                resolve(res)
                            }
                        })
                        .catch(err => {
                            reject(err)
                            fileUpload.updateTips('error', err)
                        })
                })
            },

            // 上傳切片檔案
            async buildFormDataToSend(chunkCount, file, hash, res) {
                const chunkReqArr = []
                for (let i = 0; i < chunkCount; i++) {
                    // 如果序號的檔案不存在，則上傳該分片，否則跳過
                    if (res.data[i + '.'] !== '') {
                        //  建立要上傳的檔案分片
                        const start = i * fileUpload.chunkSize
                        const end = Math.min(
                            file.size,
                            start + fileUpload.chunkSize
                        )
                        const form = new FormData()
                        form.append('file', blobSlice.call(file, start, end))
                        form.append('fileName', file.name)
                        form.append('total', chunkCount)
                        form.append('chunkSize', fileUpload.chunkSize)
                        form.append('index', i)
                        form.append('size', file.size)
                        form.append('hash', hash)
                        // 發送請求
                        const chunkReqItem = () => {
                            return $request.post(
                                // 請求接口
                                '/chunks_upload',
                                // 請求參數
                                form,
                                // 取得上傳進度
                                {
                                    onUploadProgress: e => {
                                        // e為ProgressEvent，當loaded===total表示切片上傳完成
                                        if (e.loaded === e.total) {
                                            fileUpload.chunkDoneTotal += 1
                                        }
                                    },
                                    timeout: 0,
                                }
                            )
                        }
                        chunkReqArr.push(chunkReqItem)
                    }
                }

                // 設定目前進度條
                fileUpload.chunkDoneTotal =
                    fileUpload.chunkTotal - chunkReqArr.length

                // 如果切片數量小於10，一口氣全部上傳
                if (chunkReqArr.length <= 10) {
                    let requestArray = []
                    for (let item of chunkReqArr) {
                        requestArray.push(item())
                    }

                    Promise.all(requestArray)
                        .then(() => {
                            fileUpload.updateTips(
                                'success',
                                '分片全部上傳完成，正在合併所有的分割檔案，請稍後...'
                            )
                            // 請求合併檔案
                            fileUpload.postChunkMerge(
                                fileUpload.chunkSize,
                                file.name,
                                fileUpload.chunkTotal,
                                fileUpload.fileHash
                            )
                        })
                        .catch(err => {
                            console.log(err)
                            fileUpload.updateTips(
                                'error',
                                '上傳被中斷，請確認網路環境後重新上傳'
                            )
                            return
                        })
                } else {
                    // 如果切片數量大於10，則逐個上傳
                    for (let item of chunkReqArr) {
                        try {
                            await item()
                        } catch (error) {
                            fileUpload.updateTips(
                                'error',
                                '上傳被中斷，請確認網路環境後重新上傳'
                            )
                            return
                        }
                    }
                    fileUpload.updateTips('success', '分片全部上傳完成')
                    // 請求合併檔案
                    fileUpload.postChunkMerge(
                        fileUpload.chunkSize,
                        file.name,
                        fileUpload.chunkTotal,
                        fileUpload.fileHash
                    )
                }
            },

            // 切片檔案全部上傳完畢，發送合併請求
            async postChunkMerge(chunkSize, name, chunkTotal, fileHash) {
                $request
                    .post(
                        '/chunks_merge',
                        {
                            chunkSize: chunkSize,
                            fileName: name,
                            total: chunkTotal,
                            hash: fileHash,
                        },
                        {
                            timeout: 0,
                        }
                    )
                    .then(res => {
                        if (res.status === 'success') {
                            fileUpload.chunkDoneTotal = chunkTotal
                            fileUpload.updateTips(
                                'success',
                                '文件分片上傳完成並合併成功！'
                            )
                        } else {
                            fileUpload.updateTips('error', res.message)
                        }
                    })
                    .catch(err => {
                        fileUpload.updateTips('error', err)
                    })
            },
        })

        // 頁面上傳檔案對應功能
        const fileButtons = {
            // 新增檔案
            addFile: element => {
                fileUpload.file = element.target.files[0]
                fileUpload.getFile()
            },

            // 上傳檔案
            startUpload: async () => {
                if (!fileUpload.allowUpload) {
                    fileUpload.updateTips(
                        'warning',
                        '正在讀取文件計算hash中，請稍等'
                    )
                    return
                }

                fileUpload.chunkDoneTotal = 0

                // 1.確認文件是否存在
                if (!fileUpload.file) {
                    fileUpload.updateTips(
                        'error',
                        '讀取文件失敗，請重新選擇要上傳的文件'
                    )
                    return
                }

                // 2.判斷檔案當前上傳狀態（16=已上傳完成 17=未上傳 18=需續傳 19=全部切片上傳完成，需要合併　20=檔案已經存在，無需上傳　)
                const res = await fileUpload.postFileHash()
                if (res && res.code == 16) {
                    fileUpload.updateTips(
                        'warning',
                        '該檔案已上傳，無需重覆上傳'
                    )
                    return
                } else if (res && res.code == 19) {
                    fileUpload.updateTips(
                        'warning',
                        '檢查成功，所有切片已上傳完畢，開始進行合併！'
                    )
                } else {
                    fileUpload.updateTips('warning', '文件分片正在上傳中...')
                }
                // 3.初始化對應請求Queue並執行
                await fileUpload.buildFormDataToSend(
                    fileUpload.chunkTotal,
                    fileUpload.file,
                    fileUpload.fileHash,
                    res
                )
            },
        }

        return {
            fileUpload,
            fileButtons,
        }
    },
}
</script>


