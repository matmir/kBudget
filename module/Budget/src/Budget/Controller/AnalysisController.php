<?php

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Form\TransactionTimeSelectForm;
use Budget\Form\TransactionTimeSelectFormFilter;

/**
 * Analysis controller
 * 
 * @author Mateusz Mirosławski
 * 
 */
class AnalysisController extends BaseController
{
    /**
     * Prepare filter form. Return array ('form', 'dateParam', 'aid')
     * 
     * @param int $uid User identifier
     * 
     * @return array
     */
    private function prepareFilterForm($uid)
    {
        // Get user bank accounts to select object
        $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);

        // Filtering form
        $form = new TransactionTimeSelectForm();

        // Date params
        $date_param = array();

        // Get date type param
        $dateType = $this->params()->fromRoute('dateType', 'month');

        // Get account identifier
        $aid = (int) $this->params()->fromRoute('aid', 0);

        // Check if given bank account is correct
        if ($aid == 0) {
            // Load default account id
            $aid = $this->get('User\UserMapper')->getUser($uid)->default_aid;
        } else { // Is some account identifier
            // Check if given account id is user accout
            if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
                // Load default account id
                $aid = $this->get('User\UserMapper')->getUser($uid)->default_aid;
            }
        }

        // Insert bank accounts into the form
        $form->get('aid')->setValueOptions($accounts);
        $form->get('aid')->setValue($aid);

        $form->get('year')->setValue(date('Y'));
        $form->get('month')->setValue(date('m'));
        $form->get('date_to')->setValue(date('Y-m-d'));
        $form->get('date_from')->setValue(date('Y-m-d'));

        if ($dateType == 'month') {

            // Get date params
            $Y = (int) $this->params()->fromRoute('year', date('Y'));
            $m = (int) $this->params()->fromRoute('month', date('m'));

            // Insert into the form
            $form->get('aid')->setValue($aid);
            $form->get('filter_type')->setValue($dateType);
            $form->get('month')->setValue($m);
            $form->get('year')->setValue($Y);

            // Prepare date params
            $date_param = array(
                'type' => 'month',
                'dt_month' => (new \DateTime($Y.'-'.$m.'-01'))->format('Y-m'),
            );
        } else if ($dateType == 'between') {

            // Actual date
            $actualDate = new \DateTime();

            // Get up date params
            $YUP = (int) $this->params()->fromRoute('yearUp', date('Y'));
            $mUp = (int) $this->params()->fromRoute('monthUp', date('m'));
            $dUp = (int) $this->params()->fromRoute('dayUp', date('d'));
            $dateUp = new \DateTime($YUP.'-'.$mUp.'-'.$dUp);
            // Get down date params
            $YDown = (int) $this->params()->fromRoute('yearDown', date('Y'));
            $mDown = (int) $this->params()->fromRoute('monthDown', date('m'));
            $dDown = (int) $this->params()->fromRoute('dayDown', date('d'));
            $dateDown = new \DateTime($YDown.'-'.$mDown.'-'.$dDown);

            // Check if given up date is correct
            if ($dateUp > $actualDate) {
                $dateUp = $actualDate;
            }

            // Insert into the form
            $form->get('aid')->setValue($aid);
            $form->get('filter_type')->setValue($dateType);
            $form->get('date_from')->setValue($dateDown->format('Y-m-d'));
            $form->get('date_to')->setValue($dateUp->format('Y-m-d'));

            // Prepare date params
            $date_param = array(
                'type' => 'between',
                'dt_up' => $dateUp->format('Y-m-d'),
                'dt_down' => $dateDown->format('Y-m-d'),
            );
        } else { // All transactions

            // Insert into the form
            $form->get('aid')->setValue($aid);
            $form->get('filter_type')->setValue($dateType);

            // Prepare date params
            $date_param = array(
                'type' => 'all'
            );
        }

        return array(
            'form' => $form,
            'dateParam' => $date_param,
            'aid' => $aid
        );
    }

    /**
     * Main action
     */
    public function indexAction()
    {
    }
    
    // Podział na kategorie
    public function categoryAction()
    {
        // Get user identifier
        $uid = $this->get('userId');

        // Get search params
        $searchParams = $this->prepareFilterForm($uid);

        // Prepare data for the expenses pie
        $expenseData = $this->get('Budget\AnalysisService')->makeCategoryPieData(
            $uid,
            $searchParams['aid'],
            $searchParams['dateParam'],
            array(1, 2) // Expenses and outgoing transfers
        );

        // Prepare data for the profits pie
        $profitData = $this->get('Budget\AnalysisService')->makeCategoryPieData(
            $uid,
            $searchParams['aid'],
            $searchParams['dateParam'],
            array(0, 3) // Profits and incoming transfers
        );

        // Prepare chart subtitle
        $subtitle = '';
        $dtP = $searchParams['dateParam'];
        if ($dtP['type'] == 'month') {
            $subtitle = 'Miesiąc: '.$dtP['dt_month'];
        } else if ($dtP['type'] == 'between') {
            $subtitle = 'Zakres od '.$dtP['dt_down'].' do '.$dtP['dt_up'];
        } else if ($dtP['type'] == 'all') {
            $subtitle = 'Wszystkie dane';
        }

        return array(
            'form' => $searchParams['form'],
            'expenseData' => $expenseData,
            'profitData' => $profitData,
            'subtitle' => $subtitle
        );
    }

    /**
     * Generate time chart action
     */
    public function timeAction()
    {
        // Get user identifier
        $uid = $this->get('userId');

        // Get search params
        $searchParams = $this->prepareFilterForm($uid);

        // Get sum of expenses and profits
        $balanceData = $this->get('Budget\AnalysisService')->makeTransactionsBalanceData(
            $uid,
            $searchParams['aid'],
            $searchParams['dateParam']
        );

        // Prepare data for the time chart of expenses
        $expenseData = $this->get('Budget\AnalysisService')->makeTransactionTimeData(
            $uid,
            $searchParams['aid'],
            $searchParams['dateParam'],
            array(1, 2) // Expenses and outgoing transfers
        );

        // Prepare data for the time chart of profits
        $profitData = $this->get('Budget\AnalysisService')->makeTransactionTimeData(
            $uid,
            $searchParams['aid'],
            $searchParams['dateParam'],
            array(0, 3) // Profits and incoming transfers
        );
        
        return array(
            'form' => $searchParams['form'],
            'expenseData' => $expenseData,
            'profitData' => $profitData,
            'balanceData' => $balanceData
        );
    }

}
