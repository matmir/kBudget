<?php

namespace Budget\Service;

use Base\Service\BaseService;
use Budget\Model\Transaction;

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
            if ($prev_label == $transaction->getDate()->format('Y-m-d')) {
                
                // The same date - sum of the transactions!
                $data['data'][$i] += $transaction->getValue();
                
            } else { // Date is different
                
                // Insert into the array
                array_push($data['data'], $transaction->getValue());
                array_push($data['labels'], $transaction->getDate()->format('Y-m-d'));
                $prev_label = $transaction->getDate()->format('Y-m-d');
                
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
            array(Transaction::EXPENSE, Transaction::OUTGOING_TRANSFER) // Expenses and outgoing transfers
        );
        // Get sum of profits
        $profits = $this->getServiceLocator()->get('Budget\TransactionMapper')->getSumOfTransactions(
            $uid,
            $aid,
            $dateParams,
            array(Transaction::PROFIT, Transaction::INCOMING_TRANSFER) // Profits and incoming transfers
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
            if (isset($cidData[$transaction->getCategoryId()])) {
                $cidData[$transaction->getCategoryId()] += (float) $transaction->getValue();
            } else {
                $cidData[$transaction->getCategoryId()] = (float) $transaction->getValue();
                // Insert category identifier into the array
                array_push($cids, $transaction->getCategoryId());
            }

            // Sum values of the transfers
            if ($transaction->getTransferAccountId() !== null) {
                if (isset($taidData[$transaction->getTransferAccountId()])) {
                    $taidData[$transaction->getTransferAccountId()] += (float) $transaction->getValue();
                } else {
                    $taidData[$transaction->getTransferAccountId()] = (float) $transaction->getValue();
                    // Insert account identifier into the array
                    array_push($taids, $transaction->getTransferAccountId());
                }
            }
            
        }

        // Check if there are categories to load
        if (!empty($cids)) {

            // Get required categories
            $categories = $this->getServiceLocator()->get('User\CategoryMapper')->getCategoriesWithGivenIds(
                $uid,
                $cids
            );

            foreach ($categories as $category) {

                // Main cateogry
                if ($category->getParentCategoryId() === null) {
                    $returnData[$category->getCategoryName()] = array('value' => $cidData[$category->getCategoryId()]);
                    $categoryNames[$category->getCategoryId()] = $category->getCategoryName();
                } else {
                    // Get parent identifiers
                    if (!in_array($category->getParentCategoryId(), $cids)) {
                        array_push($pcids, $category->getParentCategoryId());
                    }
                }
            }

            // Check if there are main categories
            if (!empty($pcids)) {

                // Get main categories
                $mainCategories = $this->getServiceLocator()->get('User\CategoryMapper')->getCategoriesWithGivenIds(
                    $uid,
                    $pcids
                );

                foreach ($mainCategories as $category) {

                    $returnData[$category->getCategoryName()] = array('value' => 0);
                    $categoryNames[$category->getCategoryId()] = $category->getCategoryName();

                }

                foreach ($categories as $category) {

                    // Parse only children
                    if ($category->getParentCategoryId() !== null) {
                        $returnData[$categoryNames[$category->getParentCategoryId()]]['drilldown'][$category->getCategoryName()] = $cidData[$category->getCategoryId()];
                        $returnData[$categoryNames[$category->getParentCategoryId()]]['value'] += $cidData[$category->getCategoryId()];
                    }

                }

            }

        }
        
        // Check transfers
        if (!empty($taids)) {
            $accounts = $this->getServiceLocator()->get('User\AccountMapper')->getAccountsWithGivenIds(
                $uid,
                $taids
            );

            foreach ($accounts as $account) {
                $returnData[$categoryNames[$transferCategoryId]]['drilldown'][$account->getAccountName()] = $taidData[$account->getAccountId()];
            }
        }

        return $returnData;
    }
}
