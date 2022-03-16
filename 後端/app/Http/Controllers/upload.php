<?php

namespace App\Http\Controllers;

// ini_set('post_max_size', '10000M');
// ini_set('upload_max_filesize', '10000M');
// ini_set('max_execution_time', '600');

use App\Http\Controllers\Common\ApiResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class upload extends Controller
{
    use ApiResponse;

    /**
     * 檢查切片檔案是否存在
     *
     * @param Request $request
     * @return object
     */
    public function hashCheck(Request $request)
    {
        // Request參數檢查
        $validator = Validator::make(
            [
                'hash' => 'required|mimes: mp4',
                'chunkSize' => 'required',
                'total' => 'required',
                'fileName' => 'required',
            ],
            [
                'hash.required' => 'hash不可為空',
                'chunkSize.required' => 'chunkSize不可為空',
                'total.required' => 'total不可為空',
                'fileName.required' => 'fileName不可為空',
            ]
        );

        // 參數不通過回傳
        if ($validator->fails()) {
            return $this->apiResponse(2, false, $validator->errors());
        }

        // 切片路徑
        $chunkPath = $request->hash . '-' . $request->chunkSize;

        // 判斷目錄是否存在（若未存在則代表從未上傳）
        if (storage::disk('local')->exists($chunkPath)) {
            // 判斷合併後檔案是否存在，若存在代表無需上傳
            if ($readlFile = Storage::disk('local')->exists($chunkPath . '/' . $request->fileName)) {
                return $this->apiResponse(16, true, storage::files($chunkPath));
            } else {
                //　目錄存在且並無合併檔案存在，開始內部檔案數量
                $files = storage::files($chunkPath);
                $fileCount = count($files);

                $indexArray = [];

                // 取得已上傳完成的所有文件序號（已上傳可以實現秒傳）
                foreach ($files as $file) {
                    // 預設檔名（取最後一個數字）：38b091290650a8f396889f18ec839816-10485760/38b091290650a8f396889f18ec839816-0
                    if (count(explode('-', $file)) > 2) {
                        $index = explode('-', $file)[2];
                        $indexArray[$index . '.'] = '';
                    }
                }

                // 如果目錄檔案數量不為零 且 目錄檔案數量等於切割檔案數量（代表需要合併，但尚未合併）
                if ($fileCount === $request->total && $fileCount !== 0) {
                    return $this->apiResponse(19, true, $indexArray);
                } else {
                    return $this->apiResponse(18, true, $indexArray);
                }
            }

        } else {
            // 回傳檔案從未上傳的訊息
            return $this->apiResponse(17, true);
        }

    }

    /**
     * 切片檔案上傳
     *
     * @param Request $request
     * @return object
     */
    public function chunksUpload(Request $request)
    {
        $validator = Validator::make(
            [
                'file' => 'required', //檔案
                'fileName' => 'required', //檔案名稱
                'total' => 'required', //總數
                'chunkSize' => 'required', //切片大小
                'index' => 'required', //第幾個切片
                'hash' => 'required', //切片hash名稱
            ],
            [
                'file.required' => 'file不可為空',
                'fileName.required' => 'fileName不可為空',
                'total.required' => 'total不可為空',
                'chunkSize.required' => 'chunkSize不可為空',
                'index.required' => 'index不可為空',
                'hash.required' => 'hash不可為空',
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponse(2, false, $validator->errors());
        }

        var_dump($request->file);

        if ($request->hasfile('file')) {
            $chunkPath = $request->hash . '-' . $request->chunkSize . '/' . $request->hash . '-' . $request->index;
            storage::disk('local')->put($chunkPath, file_get_contents($request->file));
            return $this->apiResponse(20, true);
        } else {
            return $this->apiResponse(24, false);
        }

    }

    /**
     * 切片檔案合併
     *
     * @param Request $request
     * @return object
     */
    public function chunks_merge(Request $request)
    {
        $validator = Validator::make(
            [
                'fileName' => 'required', //檔案名稱
                'total' => 'required', //總數
                'chunkSize' => 'required', //切片大小
                'hash' => 'required', //切片hash名稱
            ],
            [
                'fileName.required' => 'fileName不可為空',
                'total.required' => 'total不可為空',
                'chunkSize.required' => 'chunkSize不可為空',
                'hash.required' => 'hash不可為空',
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponse(2, false, $validator->errors());

        }
        $start = memory_get_usage();

        // 取得切片路徑
        $chunkPath = $request->hash . '-' . $request->chunkSize;

        // 取得所有的Chunks的數量
        $chunksCount = count(storage::files($chunkPath));

        // 如果現在切片數量 與 檔案總數不符，或者目錄內沒有檔案
        if ($chunksCount !== $request->total || $chunksCount === 0) {
            return $this->apiResponse(22, false, [
                '總數' => $chunksCount,
                '檔案' => storage::files($chunkPath),
            ]);
        }

        // 切片合併（合併順序必須由0-n，若順序不對，則檔案合併後也會是錯誤的）
        for ($i = 0; $i < $chunksCount; $i++) {
            try {
                // 讀取切片
                $blob = Storage::disk('local')->get($chunkPath . '/' . $request->hash . '-' . $i);
                // 如果取不到切片檔案
            } catch (FileNotFoundException $e) {
                return $this->apiResponse(23, false, "第${i}切片檔案不存在，合併失敗！");
            }

            // 切片合併
            file_put_contents(storage_path('app/' . $chunkPath . '/' . $request->fileName), $blob, FILE_APPEND);
            // 合併後將切片刪除
            storage::disk('local')->delete($chunkPath . '/' . $request->hash . '-' . $i);

        }

        $end = memory_get_usage();
        // 回傳
        return $this->apiResponse(23, true, [
            '程式開始時的記憶體用量' => $start / 1000 / 1000 . 'MB',
            '程式結束時的記憶體用量' => $end / 1000 / 1000 . 'MB',
            '程式使用了多少記憶體' => ($end - $start) / 1000 / 1000 . 'MB',
        ]);

    }
}
