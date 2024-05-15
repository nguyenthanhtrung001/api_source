<?php
/**
 * Category Controller
 */
class CategoryController extends Controller
{
    /**
     * Process
     */
    public function process()
    {
        $Route = $this->getVariable("Route");
        // $AuthUser = $this->getVariable("AuthUser");

        // // Auth
        // if (!$AuthUser){
        //     header("Location: ".APPURL."/login");
        //     exit;
        // } 

        $Route = $this->getVariable("Route");

        if (!isset($Route->params->id)) {
            $this->resp->msg = __("ID is required!");
            $this->jsonecho();
        }

        $Category = Controller::model("Category", $Route->params->id);
        if (!$Category->isAvailable()) {
            $this->resp->msg = __("Category doesn't exist!");
            $this->jsonecho();
        }

        $this->setVariable("Category", $Category);

        $request_method = Input::method();
        if($request_method === 'PUT'){
            $this->save();
        }else if($request_method === 'GET'){
            $this->getById();
        }else if($request_method === 'DELETE'){
            $this->remove();
        }
    }

    private function save()
    {
        $this->resp->result = 0;
        $Category = $this->getVariable("Category");
    
        // Kiểm tra và nhận giá trị category_balance_result_income
        $category_balance_result_income = Input::put("category_balance_result_income");
        if ($category_balance_result_income !== null) {
            $this->saveIncomeCategory($Category, $category_balance_result_income);
        } else {
            $category_balance_result = Input::put("category_balance_result");
            if (!$category_balance_result) {
                $this->resp->msg = __("Missing category_balance_result.");
                $this->jsonecho();
            }
        
            $this->saveExpenseCategory($Category, $category_balance_result);
        }
    }
    
    private function saveIncomeCategory($Category, $category_balance_result_income)
    {

        $persentId = (int) $Category->get("persent");
        
        // Cập nhật số dư của các danh mục thu nhập
        $loanPercentages = [55, 10, 10, 10, 10, 5]; // % cho mỗi khoản thu nhập
        $totalIncome = $category_balance_result_income; // Tổng thu nhập
    
        // Lấy danh sách các danh mục cần cập nhật số dư
        $percentIds = [6, 5, 4, 3, 2, 1];
    
        foreach ($percentIds as $index => $percentId) {
            // Tính toán số dư mới dựa trên tỷ lệ phần trăm
            $balance = $totalIncome * ($loanPercentages[$index] / 100);
    
            // Cập nhật số dư mới vào số dư hiện tại của danh mục
            DB::table(TABLE_PREFIX . 'categories')
                ->where('percent', '=', $percentId)
                ->where('user_id', '=', $Category->get("user_id")) // Sử dụng user_id của Category
                ->update(['balance' => DB::raw('balance + ' . $balance)]);
        }
    
        // Trả về kết quả thành công
        $this->resp->result = 1;
        $this->resp->category = (int) $Category->get("id");
        $this->resp->msg = __("Category has been updated successfully!");
        $this->jsonecho();
    }
    
    private function saveExpenseCategory($Category, $category_balance_result)
    {
        $categoryId = (int) $Category->get("id");
        $percent = (int) $Category->get("percent");
        // Cập nhật số dư của các danh mục chi tiêu
        // DB::table(TABLE_PREFIX . 'categories')
        //     ->where('id', '=', $categoryId)
        //     ->update(['balance' => $category_balance_result]);


            // // Trong phương thức saveExpenseCategory()
            DB::table(TABLE_PREFIX . 'categories')
            //->where('percent', '=', $percent)
            ->where('id', '=', $categoryId)
           
            ->update(['balance' => $category_balance_result]);

    
        // Trả về kết quả thành công
        $this->resp->result = 1;
        $this->resp->category = (int) $Category->get("id");
        $this->resp->msg = __("Category has been updated successfully!");
        $this->jsonecho();
    }
    
    





    /**
     * get category by id
     * @return void 
     */
    private function getById()
    {
        
        $this->resp->result = 0;
        // $AuthUser = $this->getVariable("AuthUser");
        $Category = $this->getVariable("Category");

        $this->resp->result = 1;
        $this->resp->data = array(
            "id" => (int) $Category->get("id"),
            "type" => (int) $Category->get("type"),
            "name" => $Category->get("name"),
            "description" => $Category->get("description"),
            "color" => "#".$Category->get("color")
        );
        $this->jsonecho();
        
        
    }


    /**
     * Remove Category
     * @return void 
     */
    private function remove()
    {
        /**Step 1 */
        $this->resp->result = 0;        
        $AuthUser = $this->getVariable("AuthUser");
        $Category = $this->getVariable("Category");

        /**Step 4 */
        // check transactions
        $Transactions = Controller::model("Transactions");
        $Transactions->where("category_id", "=", $Category->get("id"))
                    ->where("user_id", "=", $AuthUser->get("id"))
                    ->fetchData();

        if( $Transactions->getTotalCount() > 0 ){
            $this->resp->msg = __("There are transactions with this category ID!");
            $this->jsonecho();
        }


        /**Step 5 */
        // check budgets
        $Budgets = Controller::model("Budgets");
        $Budgets->where("category_id","=", $Category->get("id") )
                ->where("user_id", "=", $AuthUser->get("id"))
                ->fetchData();

        if( $Budgets->getTotalCount() > 0 )
        {
            $this->resp->msg = __("There are budgets with this category ID!");
            $this->jsonecho();
        }

        $Category->delete();

        /**Step 6 */
        $this->resp->result = 1;
        $this->resp->category = (int) $Category->get("id");
        $this->resp->msg = __("Category has been deleted successfully");
        $this->jsonecho();
    }
}