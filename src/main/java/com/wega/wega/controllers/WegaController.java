package com.wega.wega.controllers;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class WegaController {


    @GetMapping("/blog")
    public String blogMain(Model model){
        return "blog-main";
    }

}
