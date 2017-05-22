//
//  Login.swift
//  UBCollaborate
//
//  Created by Onur Tuna on 2017-05-14.
//  Copyright © 2017 Onur Tuna. All rights reserved.
//

import UIKit

class Login: UIViewController {
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        // Do any additional setup after loading the view.
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    
    var jsonData: Any!
    
    @IBOutlet weak var login_username: UITextField!

    
    @IBOutlet weak var login_password: UITextField!
    
    
    @IBAction func sendLoginRequest(_ sender: UIButton) {
        
        var request = URLRequest(url: URL(string: "https://ryanwirth.ca/misc/ubcollaborate/login.php")!)
        
        request.httpMethod = "POST"
        let queryString = "email=\(login_username.text!)&password=\(login_password.text!)"
        
        request.httpBody = queryString.data(using: .utf8)
        
        // check for existence of data
        let task = URLSession.shared.dataTask(with: request){data, response, error in
            guard let data = data, error == nil else{
                print("error: \(error)")
                return
            }
            
            // check for http errors
            if let httpStatus = response as? HTTPURLResponse,
                httpStatus.statusCode != 200 {
                
                print("statusCode should be 200, but is \(httpStatus.statusCode)")
                print("response = \(response)")
            }
            
            
            // return the data as json and store it in a global variable in json format
            self.jsonData = try? JSONSerialization.jsonObject(with: data, options: [])
            print(self.jsonData)

            
        }
        task.resume()
    }
    
    
    /*
     // MARK: - Navigation
     
     // In a storyboard-based application, you will often want to do a little preparation before navigation
     override func prepare(for segue: UIStoryboardSegue, sender: Any?) {
     // Get the new view controller using segue.destinationViewController.
     // Pass the selected object to the new view controller.
     }
     */
    
}
