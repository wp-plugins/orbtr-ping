<?php

if (!class_exists('Pager')):

class Pager
{
	
	private $uri;
	
	public function __construct($uri) {
		$this->uri = $uri;	
	}
	
	public function findStart($limit) 
	{
		if ((!isset($_GET['p'])) || ($_GET['p'] == "1")) {
			$start = 0;
			$_GET['p'] = 1;
		} else {
			$start = ($_GET['p']-1) * $limit;
		}
		return $start;
	}
	  
	  /*
	   * int findPages (int count, int limit)
	   * Returns the number of pages needed based on a count and a limit
	   */
	public function findPages($count, $limit) 
	{
		 $pages = (($count % $limit) == 0) ? $count / $limit : floor($count / $limit) + 1;
	 
		 return $pages;
	}
	 
	/*
	* string pageList (int curpage, int pages)
	* Returns a list of pages in the format of "« < [pages] > »"
	**/
	public function pageList($curpage, $pages, $count)
	{
       $page_list   = '';
		
       if ($pages > 1)
		{	
			
			$page_list  .= '<div class="tablenav-pages">';
			$page_list  .= '<span class="displaying-num">' . $count . ' items</span>';
			$page_list  .= '<span class="pagination-links">';
			$page_list  .= '<a class="first-page" title="Go to the first page" href="' . $this->uri . '&amp;p=1">&laquo;</a>';
			$page_list  .= '<a class="prev-page" title="Go to the previous page" href="' . $this->uri . '&amp;p=' . ($curpage-1) . '">&lsaquo;</a>';
			$page_list  .= '<span class="paging-input">' . $curpage . ' of <span class="total-pages">' . $pages . '</span></span>';
			$page_list  .= '<a class="next-page" title="Go to the next page" href="' . $this->uri . '&amp;p=' . ($curpage+1) . '">&rsaquo;</a>';
			$page_list  .= '<a class="last-page" title="Go to the last page" href="' . $this->uri . '&amp;p=' . $pages . '">&raquo;</a>';
			$page_list  .= '</span>';
			$page_list  .= '</div>';
		}
        
		return $page_list;
	}
	  
	/*
	* string nextPrev (int curpage, int pages)
	* Returns "Previous | Next" string for individual pagination (it's a word!)
	*/
	public function nextPrev($curpage, $pages)
	{
	 $next_prev  = "";
	 
		if (($curpage-1) <= 0) {
			$next_prev .= "Previous";
		} else {
			$next_prev .= "<a href=\"".$this->uri."&amp;p=".($curpage-1)."\">Previous</a>";
		}
	 
			$next_prev .= " | ";
	 
		if (($curpage+1) > $pages) {
			$next_prev .= "Next";
		} else {
			$next_prev .= "<a href=\"".$this->uri."&amp;p=".($curpage+1)."\">Next</a>";
		}
			return $next_prev;
	}
}

endif;