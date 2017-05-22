//
//  ForgotPassword.swift
//  UBCollaborate
//
//  Created by Onur Tuna on 2017-05-21.
//  Copyright © 2017 Onur Tuna. All rights reserved.
//

import UIKit

class ForgotPassword: UIViewController {
    
    
    @IBOutlet weak var fp_username: UITextField!
    
    @IBOutlet weak var fp_newPassword: UITextField!
    
    @IBOutlet weak var fp_confirmPassword: UITextField!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        // Do any additional setup after loading the view.
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    @IBAction func sendRequest(_ sender: Any) {
        
        emptyFieldCheck()
        
        
        if(!passCheck(text1: fp_newPassword.text!, text2: fp_confirmPassword.text!)){
            passAlert()
            return
        }
        
        
        
        var request = URLRequest(url: URL(string: "https://ryanwirth.ca/misc/ubcollaborate/registration.php")!)
        request.httpMethod = "POST"
        
        let queryString = "username=\(fp_username)&password=\(fp_newPassword)&password_verify=\(fp_confirmPassword)"
        
        request.httpBody = queryString.data(using: .utf8)
        
        let task = URLSession.shared.dataTask(with: request){data,response, error in
            guard let data = data, error == nil else{
                print("error: \(error)")
                return
            }
        
            if let httpStatus = response as? HTTPURLResponse,
                httpStatus.statusCode != 200 {
                print("statusCode should be 200, but is \(httpStatus.statusCode)")
                print("response = \(response)")
                return
            }
        
        }
        task.resume()
    }
    
    //checks whether the passwords are matching
    
    func passCheck(text1: String, text2: String)-> Bool{
        return text1 == text2
    }
    
    
    //returns an alert if passwords are not matching
    
    func passAlert(){
        
        let alert = UIAlertController(title: "", message: "Passwords do not match!", preferredStyle: UIAlertControllerStyle.alert)
        
        alert.addAction(UIAlertAction(title: "Ok", style: .default, handler: { (action: UIAlertAction!) in
            
        }))
        
        present(alert, animated: true, completion: nil)
        fp_confirmPassword.text! = ""

    }
    
    // checks whether any of the fields are empty
    
    func emptyFieldCheck(){
        if(fp_confirmPassword.text?.trimmingCharacters(in: NSCharacterSet.whitespacesAndNewlines) == ""){
            fp_confirmPassword.text = "You have to confirm your new password!"
            fp_confirmPassword.textColor = UIColor.red
            return
        }
        
        if(fp_username.text?.trimmingCharacters(in: NSCharacterSet.whitespacesAndNewlines) == ""){
            fp_username.text = "You have to enter your username!"
            fp_username.textColor = UIColor.red
            return
        }
        
        if(fp_newPassword.text?.trimmingCharacters(in: NSCharacterSet.whitespacesAndNewlines) == ""){
            fp_newPassword.text = "You have to enter your new password!"
            fp_newPassword.textColor = UIColor.red
            return
        }
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
