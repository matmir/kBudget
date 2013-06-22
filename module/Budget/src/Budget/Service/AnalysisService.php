<?php

namespace Budget\Service;

use Base\Service\BaseService;

/**
 * Analysis service (Prepare data for the Highcharts)
 * 
 * @author Mateusz Mirosławski
 *
 */
class AnalysisService extends BaseService
{
    /**
     * Prepare data with user transactions for the time chart.
     * Return array('data', 'labels')
     * 
     * @param int $uid User identifier
     * @param int $aid Bank account identifier
     * @param array $dateParams Search date params
     * @param array $trType Array with transaction types
     * 
     * @return array
     */
    public function makeTransactionTimeData($uid, $aid, array $dateParams, array $trType)
    {
        // Get transactions
        $transactions = $this->getServiceLocator()->get('Budget\TransactionMapper')->getTransactions(
            $uid,
            $aid,
            $dateParams,
            $trType
        );

        $data = array(
            'data' => array(),
            'labels' => array(),
        );
        
        $i=-1;
        $prev_label = '0';
        foreach ($transactions as $transaction) {
            
            // Check if the previous date is different than actual
            if ($prev_label == $transaction->t_date) {
                
                // The same date - sum of the transactions!
                $data['data'][$i] += $transaction->t_value;
                
            } else { // Date is different
                
                // Insert into the array
                array_push($data['data'], $transaction->t_value);
                array_push($data['labels'], $transaction->t_date);
                $prev_label = $transaction->t_date;
                
                $i++;
            }
        }

        // Reverse arrays
        $data['data'] = array_reverse($data['data']);
        $data['labels'] = array_reverse($data['labels']);

        return $data;
    }

    /**
     * Prepare data with sum of user transactions.
     * Return array('expenses', 'profits', 'balance')
     * 
     * @param int $uid User identifier
     * @param int $aid Bank account identifier
     * @param array $dateParams Search date params
     * 
     * @return array
     */
    public function makeTransactionsBalanceData($uid, $aid, array $dateParams)
    {
        // Get sum of expenses
        $expenses = $this->getServiceLocator()->get('Budget\TransactionMapper')->getSumOfTransactions(
            $uid,
            $aid,
            $dateParams,
            array(1, 2) // Expenses and outgoing transfers
        );
        // Get sum of profits
        $profits = $this->getServiceLocator()->get('Budget\TransactionMapper')->getSumOfTransactions(
            $uid,
            $aid,
            $dateParams,
            array(0, 3) // Profits and incoming transfers
        );
        // Balance
        $balance = $profits - $expenses;

        return array(
            'expenses' => $expenses,
            'profits' => $profits,
            'balance' => $balance
        );
    }

    /**
     * Prepare data with transaction categorized by categories
     * Return array('expenses', 'profits', 'balance')
     * 
     * @param int $uid User identifier
     * @param int $aid Bank account identifier
     * @param array $dateParams Search date params
     * @param array $trType Array with transaction types
     * 
     * @return array
     */
    public function makeCategoryPieData($uid, $aid, array $dateParams, array $trType)
    {
        $returnData = array();
        $cidData = array();
        $cids = array();
        $pcids = array();
        $categoryNames = array();
        $taidData = array();
        $taids = array();

        // Get transactions
        $transactions = $this->getServiceLocator()->get('Budget\TransactionMapper')->getTransactions(
            $uid,
            $aid,
            $dateParams,
            $trType
        );

        // Transfer category identifier
        $transferCategoryId = $this->getServiceLocator()->get('User\CategoryMapper')->getTransferCategoryId($uid);

        foreach ($transactions as $transaction) {

            // Sum values of the transactions
            if (isset($cidData[$transaction->cid])) {
                $cidData[$transaction->cid] += (float) $transaction->t_value;
            } else {
                $cidData[$transaction->cid] = (float) $transaction->t_value;
                // Insert category identifier into the array
                array_push($cids, $transaction->cid);
            }

            // Sum values of the transfers
            if ($transaction->taid !== null) {
                if (isset($taidData[$transaction->taid])) {
                    $taidData[$transaction->taid] += (float) $transaction->t_value;
                } else {
                    $taidData[$transaction->taid] = (float) $transaction->t_value;
                    // Insert account identifier into the array
                    array_push($taids, $transaction->taid);
                }
            }
            
        }

        // Get required categories
        $categories = $this->getServiceLocator()->get('User\CategoryMapper')->getCategoriesWithGivenIds(
            $uid,
            $cids
        );

        foreach ($categories as $category) {

            // Main cateogry
            if ($category->pcid === null) {
                $returnData[$category->c_name] = array('value' => $cidData[$category->cid]);
                $categoryNames[$category->cid] = $category->c_name;
            } else {
                // Get parent identifiers
                if (!in_array($category->pcid, $cids)) {
                    array_push($pcids, $category->pcid);
                }
            }
        }

        // Get main categories
        $mainCategories = $this->getServiceLocator()->get('User\CategoryMapper')->getCategoriesWithGivenIds(
            $uid,
            $pcids
        );

        foreach ($mainCategories as $category) {

            $returnData[$category->c_name] = array('value' => 0);
            $categoryNames[$category->cid] = $category->c_name;

        }

        foreach ($categories as $category) {

            // Parse only children
            if ($category->pcid !== null) {
                $returnData[$categoryNames[$category->pcid]]['drilldown'][$category->c_name] = $cidData[$category->cid];
                $returnData[$categoryNames[$category->pcid]]['value'] += $cidData[$category->cid];
            }

        }

        // Check transfers
        if (!empty($taids)) {
            $accounts = $this->getServiceLocator()->get('User\AccountMapper')->getAccountsWithGivenIds(
                $uid,
                $taids
            );

            foreach ($accounts as $account) {
                $returnData[$categoryNames[$transferCategoryId]]['drilldown'][$account->a_name] = $taidData[$account->aid];
            }
        }

        return $returnData;
    }
}
