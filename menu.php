<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommonModel extends Model
{
    use HasFactory;
    protected $APP_SESSION;

    public function __construct()
    {
        $this->APP_SESSION = config('customsettings.cusconfig.APP_SESSION');
    }


    public function system_link_list()
    {

        $str = "select tr.*,tr.MENU_LINK_NAME right_name,tr.MENU_CATE_ID categ_id, tr.MENU_GROUP_ID group_id,
			cat.MENU_CATE_NAME categ_name,gr.MENU_NAME group_name 	from MENU_LINK tr
			left outer join MENU_CATEGORY cat on(cat.ID=tr.MENU_CATE_ID)
			left outer join MENU_GROUP gr on(gr.ID=tr.MENU_GROUP_ID)
			where cat.DATA_STATUS='1' and gr.DATA_STATUS='1' and tr.DATA_STATUS='1'
			order by tr.MENU_GROUP_ID,tr.MENU_CATE_ID,tr.SORT_ORDER";
        $result = DB::select($str);
        return $result;
    }



    public function row_query()
    {


        $menus = DB::select('SELECT 
                            mg.id AS group_id,
                            mg.menu_name AS group_name,
                            mg.url_prefix AS group_url_prefix,
                            mc.id AS category_id,
                            mc.menu_cate_name AS category_name,
                            mc.url_prefix AS category_url_prefix,
                            ml.id AS link_id,
                            ml.menu_operation AS menu_operation,
                            ml.menu_link_name AS link_name,
                            ml.url_prefix AS link_url_prefix,
                            ml.route_name AS route_name
                        FROM 
                            menu_group mg
                        LEFT JOIN 
                            menu_category mc ON mg.id = mc.menu_group_id
                        LEFT JOIN 
                            menu_link ml ON mc.id = ml.menu_cate_id
                        WHERE 
                            mg.data_status = 1
                            AND mc.data_status = 1
                            AND ml.data_status = 1
                        ORDER BY 
                            mg.sort_order, mc.sort_order, ml.sort_order
                    ');

        // Process $menus array to format it as desired
        $menuGroups = [];
        foreach ($menus as $menu) {
            $groupIndex = array_search($menu->group_id, array_column($menuGroups, 'group_id'));
            if ($groupIndex === false) {
                $menuGroups[] = [
                    'group_id' => $menu->group_id,
                    'group_name' => $menu->group_name,
                    'group_url_prefix' => $menu->group_url_prefix,
                    'categories' => [],
                ];
                $groupIndex = count($menuGroups) - 1;
            }

            $categoryIndex = array_search($menu->category_id, array_column($menuGroups[$groupIndex]['categories'], 'category_id'));
            if ($categoryIndex === false) {
                $menuGroups[$groupIndex]['categories'][] = [
                    'category_id' => $menu->category_id,
                    'category_name' => $menu->category_name,
                    'category_url_prefix' => $menu->category_url_prefix,
                    'links' => [],
                ];
                $categoryIndex = count($menuGroups[$groupIndex]['categories']) - 1;
            }

            $menuGroups[$groupIndex]['categories'][$categoryIndex]['links'][] = [
                'link_id' => $menu->link_id,
                'menu_operation' => $menu->menu_operation,
                'link_name' => $menu->link_name,
                'link_url_prefix' => $menu->link_url_prefix,
                'route_name' => $menu->route_name,
            ];
        }



        return $menuGroups;

        // $menuGroups now contains the menu groups along with their categories and links



    }
}
